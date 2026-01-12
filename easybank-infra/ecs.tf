# ==========================================
# ECS Cluster & Logs
# ==========================================
resource "aws_ecs_cluster" "easybank" {
  name = "easybank-cluster"
}

resource "aws_cloudwatch_log_group" "easybank" {
  name              = "/ecs/easybank"
  retention_in_days = 7
}

# ==========================================
# ECS Task Execution IAM Role (ECS internal use)
# ==========================================
resource "aws_iam_role" "ecs_task_execution" {
  name = "ecsTaskExecutionRole"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [{
      Effect    = "Allow"
      Action    = "sts:AssumeRole"
      Principal = { Service = "ecs-tasks.amazonaws.com" }
    }]
  })
}

resource "aws_iam_role_policy_attachment" "ecs_task_execution_policy" {
  role       = aws_iam_role.ecs_task_execution.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"
}

resource "aws_iam_role_policy_attachment" "ecs_task_ecr_access" {
  role       = aws_iam_role.ecs_task_execution.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"
}

# ==========================================
# ECS Task Role (App role, e.g., SES)
# ==========================================
resource "aws_iam_role" "ecs_task_role" {
  name = "easybank-task-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [{
      Effect    = "Allow"
      Action    = "sts:AssumeRole"
      Principal = { Service = "ecs-tasks.amazonaws.com" }
    }]
  })
}

# SES send email policy
resource "aws_iam_policy" "ecs_ses_send_email" {
  name        = "ECS_SES_SendEmail"
  description = "Allow ECS tasks to send emails via SES"

  policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Effect   = "Allow",
        Action   = [
          "ses:SendEmail",
          "ses:SendRawEmail"
        ],
        Resource = "*"  # optionally restrict to your verified SES identities
      }
    ]
  })
}

resource "aws_iam_role_policy_attachment" "ecs_task_role_ses_attach" {
  role       = aws_iam_role.ecs_task_role.name
  policy_arn = aws_iam_policy.ecs_ses_send_email.arn
}

# ==========================================
# ECS Task Definition
# ==========================================
resource "aws_ecs_task_definition" "easybank" {
  family                   = "easybank"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024

  execution_role_arn = aws_iam_role.ecs_task_execution.arn
  task_role_arn      = aws_iam_role.ecs_task_role.arn

  container_definitions = jsonencode([{
    name      = "easybank"
    image     = var.easybank_image  # <-- dynamic SHA-tagged image
    essential = true

    portMappings = [{
      containerPort = 80
      protocol      = "tcp"
    }]

    logConfiguration = {
      logDriver = "awslogs"
      options = {
        awslogs-group         = "/ecs/easybank"
        awslogs-region        = var.aws_region
        awslogs-stream-prefix = "ecs"
      }
    }
  }])
}

# ==========================================
# ECS Service
# ==========================================
resource "aws_ecs_service" "easybank" {
  name            = "easybank-service"
  cluster         = aws_ecs_cluster.easybank.id
  task_definition = aws_ecs_task_definition.easybank.arn
  desired_count   = 2
  launch_type     = "FARGATE"

  network_configuration {
    subnets          = aws_subnet.app[*].id
    security_groups  = [aws_security_group.app_sg.id]
    assign_public_ip = false
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.easybank.arn
    container_name   = "easybank"
    container_port   = 80
  }

  depends_on = [aws_lb_listener.easybank]
}
