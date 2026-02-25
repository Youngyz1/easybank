resource "aws_secretsmanager_secret" "db_credentials" {
  name                    = "easybank/db-credentials"
  recovery_window_in_days = 0
}

# ✅ Single secret version with all keys — duplicate removed
resource "aws_secretsmanager_secret_version" "db_credentials" {
  secret_id = aws_secretsmanager_secret.db_credentials.id
  secret_string = jsonencode({
    username          = "easybank"
    password          = var.db_password
    host              = aws_db_instance.easybank.address
    port              = "3306"
    dbname            = "easybank"
    admin_password    = var.admin_password
    stripe_secret_key = var.stripe_secret_key
    stripe_public_key = var.stripe_public_key
  })
}

# Allow ECS task execution role to read the secret
resource "aws_iam_role_policy" "ecs_secrets_access" {
  name = "ecs-secrets-access"
  role = aws_iam_role.ecs_task_execution.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Action = [
          "secretsmanager:GetSecretValue"
        ]
        Resource = aws_secretsmanager_secret.db_credentials.arn
      }
    ]
  })
}
