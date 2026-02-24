# ==========================================
# SNS Topic for Alerts
# ==========================================
resource "aws_sns_topic" "alerts" {
  name = "easybank-alerts"
}

# ==========================================
# Lambda IAM Role for SNS → Slack
# ==========================================
resource "aws_iam_role" "lambda_sns_to_slack" {
  name = "lambda_sns_to_slack_role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [{
      Action = "sts:AssumeRole"
      Effect = "Allow"
      Principal = { Service = "lambda.amazonaws.com" }
    }]
  })
}

resource "aws_iam_role_policy_attachment" "lambda_basic_execution" {
  role       = aws_iam_role.lambda_sns_to_slack.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSLambdaBasicExecutionRole"
}

# ==========================================
# Lambda Function for SNS → Slack
# ==========================================
resource "aws_lambda_function" "sns_to_slack" {
  function_name = "sns_to_slack"
  role          = aws_iam_role.lambda_sns_to_slack.arn
  handler       = "lambda_function.lambda_handler"
  runtime       = "python3.11"

  filename = "${path.module}/lambda_sns_to_slack.zip"

  environment {
    variables = {
      SLACK_WEBHOOK_URL = var.slack_webhook_url
    }
  }

  depends_on = [aws_iam_role_policy_attachment.lambda_basic_execution]
}

# ==========================================
# Lambda Subscription to SNS (Slack integration)
# ==========================================
resource "aws_sns_topic_subscription" "lambda_subscription" {
  topic_arn = aws_sns_topic.alerts.arn
  protocol  = "lambda"
  endpoint  = aws_lambda_function.sns_to_slack.arn
}

# Allow SNS to invoke Lambda
resource "aws_lambda_permission" "allow_sns" {
  statement_id  = "AllowExecutionFromSNS"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.sns_to_slack.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.alerts.arn
}

# ==========================================
# ECS CPU Alarm
# ==========================================
resource "aws_cloudwatch_metric_alarm" "ecs_cpu_high" {
  alarm_name          = "easybank-ecs-cpu-high"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 2
  metric_name         = "CPUUtilization"
  namespace           = "AWS/ECS"
  period              = 60
  statistic           = "Average"
  threshold           = 80
  alarm_description   = "ECS CPU utilization is above 80%"
  alarm_actions       = [aws_sns_topic.alerts.arn]

  dimensions = {
    ClusterName = aws_ecs_cluster.easybank.name
    ServiceName = aws_ecs_service.easybank.name
  }
}

# ==========================================
# ECS Memory Alarm
# ==========================================
resource "aws_cloudwatch_metric_alarm" "ecs_memory_high" {
  alarm_name          = "easybank-ecs-memory-high"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 2
  metric_name         = "MemoryUtilization"
  namespace           = "AWS/ECS"
  period              = 60
  statistic           = "Average"
  threshold           = 80
  alarm_description   = "ECS Memory utilization is above 80%"
  alarm_actions       = [aws_sns_topic.alerts.arn]

  dimensions = {
    ClusterName = aws_ecs_cluster.easybank.name
    ServiceName = aws_ecs_service.easybank.name
  }
}

# ==========================================
# RDS CPU Alarm
# ==========================================
resource "aws_cloudwatch_metric_alarm" "rds_cpu_high" {
  alarm_name          = "easybank-rds-cpu-high"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 2
  metric_name         = "CPUUtilization"
  namespace           = "AWS/RDS"
  period              = 60
  statistic           = "Average"
  threshold           = 80
  alarm_description   = "RDS CPU utilization is above 80%"
  alarm_actions       = [aws_sns_topic.alerts.arn]

  dimensions = {
    DBInstanceIdentifier = aws_db_instance.easybank.id
  }
}

# ==========================================
# ALB 5XX Errors Alarm
# ==========================================
resource "aws_cloudwatch_metric_alarm" "alb_5xx_errors" {
  alarm_name          = "easybank-alb-5xx-errors"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 2
  metric_name         = "HTTPCode_ELB_5XX_Count"
  namespace           = "AWS/ApplicationELB"
  period              = 60
  statistic           = "Sum"
  threshold           = 10
  alarm_description   = "ALB is returning too many 5XX errors"
  alarm_actions       = [aws_sns_topic.alerts.arn]
  treat_missing_data  = "notBreaching"

  dimensions = {
    LoadBalancer = aws_lb.easybank.arn_suffix
  }
}

# ==========================================
# CloudWatch Dashboard
# ==========================================
resource "aws_cloudwatch_dashboard" "easybank" {
  dashboard_name = "easybank-dashboard"

  dashboard_body = jsonencode({
    widgets = [
      {
        type       = "metric"
        x          = 0
        y          = 0
        width      = 6
        height     = 6
        properties = {
          title       = "ECS CPU Utilization"
          metrics     = [["AWS/ECS", "CPUUtilization", "ClusterName", aws_ecs_cluster.easybank.name, "ServiceName", aws_ecs_service.easybank.name]]
          period      = 60
          stat        = "Average"
          region      = var.aws_region
          annotations = []
        }
      },
      {
        type       = "metric"
        x          = 6
        y          = 0
        width      = 6
        height     = 6
        properties = {
          title       = "ECS Memory Utilization"
          metrics     = [["AWS/ECS", "MemoryUtilization", "ClusterName", aws_ecs_cluster.easybank.name, "ServiceName", aws_ecs_service.easybank.name]]
          period      = 60
          stat        = "Average"
          region      = var.aws_region
          annotations = []
        }
      },
      {
        type       = "metric"
        x          = 0
        y          = 6
        width      = 6
        height     = 6
        properties = {
          title       = "RDS CPU Utilization"
          metrics     = [["AWS/RDS", "CPUUtilization", "DBInstanceIdentifier", aws_db_instance.easybank.id]]
          period      = 60
          stat        = "Average"
          region      = var.aws_region
          annotations = []
        }
      },
      {
        type       = "metric"
        x          = 6
        y          = 6
        width      = 6
        height     = 6
        properties = {
          title       = "ALB 5XX Errors"
          metrics     = [["AWS/ApplicationELB", "HTTPCode_ELB_5XX_Count", "LoadBalancer", aws_lb.easybank.arn_suffix]]
          period      = 60
          stat        = "Sum"
          region      = var.aws_region
          annotations = []
        }
      }
    ]
  })
}