# ==========================================
# SNS Topic for Alerts
# ==========================================
resource "aws_sns_topic" "alerts" {
  name = "easybank-alerts"
}

resource "aws_sns_topic_subscription" "slack_lambda" {
  topic_arn = aws_sns_topic.alerts.arn
  protocol  = "https"
  endpoint  = var.slack_webhook_url
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
        type = "metric"
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
        type = "metric"
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
        type = "metric"
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
        type = "metric"
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