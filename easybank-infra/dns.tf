# ==========================================
# Route 53 Hosted Zone
# ==========================================
resource "aws_route53_zone" "easybank" {
  name = "ofiliyoungyz.site"
}

# ==========================================
# ACM Certificate
# ==========================================
resource "aws_acm_certificate" "easybank" {
  domain_name               = "ofiliyoungyz.site"
  subject_alternative_names = ["www.ofiliyoungyz.site"]
  validation_method         = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = {
    Name = "easybank-cert"
  }
}

# ==========================================
# ACM DNS Validation Records
# ==========================================
resource "aws_route53_record" "cert_validation" {
  for_each = {
    for dvo in aws_acm_certificate.easybank.domain_validation_options : dvo.domain_name => {
      name   = dvo.resource_record_name
      record = dvo.resource_record_value
      type   = dvo.resource_record_type
    }
  }

  allow_overwrite = true
  name            = each.value.name
  records         = [each.value.record]
  ttl             = 60
  type            = each.value.type
  zone_id         = aws_route53_zone.easybank.zone_id
}

# ==========================================
# ACM Certificate Validation
# ==========================================
resource "aws_acm_certificate_validation" "easybank" {
  certificate_arn         = aws_acm_certificate.easybank.arn
  validation_record_fqdns = [for record in aws_route53_record.cert_validation : record.fqdn]
}

# ==========================================
# Route 53 A Record → ALB
# ==========================================
resource "aws_route53_record" "easybank" {
  zone_id = aws_route53_zone.easybank.zone_id
  name    = "ofiliyoungyz.site"
  type    = "A"

  alias {
    name                   = aws_lb.easybank.dns_name
    zone_id                = aws_lb.easybank.zone_id
    evaluate_target_health = true
  }
}

# WWW redirect
resource "aws_route53_record" "easybank_www" {
  zone_id = aws_route53_zone.easybank.zone_id
  name    = "www.ofiliyoungyz.site"
  type    = "A"

  alias {
    name                   = aws_lb.easybank.dns_name
    zone_id                = aws_lb.easybank.zone_id
    evaluate_target_health = true
  }
}