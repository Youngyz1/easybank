# ==========================================
# WAF Web ACL
# ==========================================
resource "aws_wafv2_web_acl" "easybank" {
  name        = "easybank-waf"
  description = "WAF for EasyBank ALB"
  scope       = "REGIONAL"
  default_action {
    allow {}
  }

  # Rule 0: Allow file uploads on register pages
  rule {
    name     = "AllowRegisterFileUploads"
    priority = 0
    action {
      allow {}
    }
    statement {
      or_statement {
        statement {
          byte_match_statement {
            search_string         = "/page-register3.php"
            positional_constraint = "CONTAINS"
            field_to_match {
              uri_path {}
            }
            text_transformation {
              priority = 0
              type     = "NONE"
            }
          }
        }
        statement {
          byte_match_statement {
            search_string         = "/page-register4.php"
            positional_constraint = "CONTAINS"
            field_to_match {
              uri_path {}
            }
            text_transformation {
              priority = 0
              type     = "NONE"
            }
          }
        }
      }
    }
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "AllowRegisterFileUploads"
      sampled_requests_enabled   = true
    }
  }

  # Rule 1: Block common SQL injection
  rule {
    name     = "AWSManagedRulesSQLiRuleSet"
    priority = 1
    override_action {
      none {}
    }
    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesSQLiRuleSet"
        vendor_name = "AWS"
      }
    }
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "SQLiRuleSet"
      sampled_requests_enabled   = true
    }
  }

  # Rule 2: Block common web exploits (XSS etc)
  rule {
    name     = "AWSManagedRulesCommonRuleSet"
    priority = 2
    override_action {
      none {}
    }
    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesCommonRuleSet"
        vendor_name = "AWS"
        rule_action_override {
          name = "SizeRestrictions_BODY"
          action_to_use {
            count {}
          }
        }
        rule_action_override {
          name = "SizeRestrictions_Querystring"
          action_to_use {
            count {}
          }
        }
      }
    }
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "CommonRuleSet"
      sampled_requests_enabled   = true
    }
  }

  # Rule 3: Block known bad inputs
  rule {
    name     = "AWSManagedRulesKnownBadInputsRuleSet"
    priority = 3
    override_action {
      none {}
    }
    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesKnownBadInputsRuleSet"
        vendor_name = "AWS"
      }
    }
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "KnownBadInputsRuleSet"
      sampled_requests_enabled   = true
    }
  }

  visibility_config {
    cloudwatch_metrics_enabled = true
    metric_name                = "easybank-waf"
    sampled_requests_enabled   = true
  }

  tags = {
    Name = "easybank-waf"
  }
}

# ==========================================
# Associate WAF with ALB
# ==========================================
resource "aws_wafv2_web_acl_association" "easybank" {
  resource_arn = aws_lb.easybank.arn
  web_acl_arn  = aws_wafv2_web_acl.easybank.arn
}
