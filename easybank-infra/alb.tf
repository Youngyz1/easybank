# ==========================================
# Application Load Balancer
# ==========================================
resource "aws_lb" "easybank" {
  name               = "easybank-alb"
  load_balancer_type = "application"
  subnets            = aws_subnet.public[*].id
  security_groups    = [aws_security_group.alb_sg.id]

  enable_deletion_protection = false

  tags = {
    Name = "easybank-alb"
  }
}

# ==========================================
# ALB Target Group (for ECS Fargate)
# ==========================================
resource "aws_lb_target_group" "easybank" {
  name        = "easybank-tg"
  port        = 8080
  protocol    = "HTTP"
  vpc_id      = aws_vpc.easybank.id
  target_type = "ip" # Required for Fargate

  health_check {
    interval            = 30
    path                = "/"
    port                = "8080"
    protocol            = "HTTP"
    timeout             = 5
    healthy_threshold   = 2
    unhealthy_threshold = 2
  }

  tags = {
    Name = "easybank-tg"
  }
}

# ==========================================
# ALB Listener
# ==========================================
resource "aws_lb_listener" "easybank" {
  load_balancer_arn = aws_lb.easybank.arn
  port              = 80
  protocol          = "HTTP"

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.easybank.arn
  }

  # Ensure listener is created after target group
  depends_on = [aws_lb_target_group.easybank]
}
