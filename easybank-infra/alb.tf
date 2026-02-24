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
  target_type = "ip"

  health_check {
    interval            = 30
    path                = "/health.php"
    port                = "8080"
    protocol            = "HTTP"
    matcher             = "200"
    timeout             = 5
    healthy_threshold   = 2
    unhealthy_threshold = 3
  }

  tags = {
    Name = "easybank-tg"
  }
}

# ==========================================
# HTTP Listener → Redirect to HTTPS
# ==========================================
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.easybank.arn
  port              = 80
  protocol          = "HTTP"

  default_action {
    type = "redirect"
    redirect {
      port        = "443"
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}

# ==========================================
# HTTPS Listener
# ==========================================
resource "aws_lb_listener" "https" {
  load_balancer_arn = aws_lb.easybank.arn
  port              = 443
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-2016-08"
  certificate_arn   = aws_acm_certificate_validation.easybank.certificate_arn

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.easybank.arn
  }

  depends_on = [aws_acm_certificate_validation.easybank]
}