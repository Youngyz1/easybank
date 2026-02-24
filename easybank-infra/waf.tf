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