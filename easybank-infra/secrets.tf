# ==========================================
# Stripe Secrets (dummy values)
# ==========================================
resource "aws_secretsmanager_secret" "stripe" {
  name = "easybank/stripe"
}

resource "aws_secretsmanager_secret_version" "stripe" {
  secret_id = aws_secretsmanager_secret.stripe.id

  secret_string = jsonencode({
    STRIPE_SECRET = "dummy_stripe_secret"
    STRIPE_PUBLIC = "dummy_stripe_public"
  })
}
