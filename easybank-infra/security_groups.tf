# ==========================================
# ALB Security Group
# ==========================================
resource "aws_security_group" "alb_sg" {
  name        = "easybank-alb-sg"
  description = "Security group for the ALB"
  vpc_id      = aws_vpc.easybank.id

  # Public HTTP/HTTPS
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # Allow all outbound (default)
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "easybank-alb-sg"
  }
}

# ==========================================
# App Security Group
# ==========================================
resource "aws_security_group" "app_sg" {
  name        = "easybank-app-sg"
  description = "Security group for App instances"
  vpc_id      = aws_vpc.easybank.id

  # Allow outbound to internet
  egress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "easybank-app-sg"
  }
}

# ==========================================
# RDS Security Group
# ==========================================
resource "aws_security_group" "rds_sg" {
  name        = "easybank-rds-sg"
  description = "Security group for RDS"
  vpc_id      = aws_vpc.easybank.id

  # Egress (default)
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "easybank-rds-sg"
  }
}

# ==========================================
# Security Group Rules (separate to avoid cycles)
# ==========================================

# ALB → App (ingress)
resource "aws_security_group_rule" "alb_to_app" {
  type                     = "ingress"
  from_port                = 80
  to_port                  = 80
  protocol                 = "tcp"
  security_group_id        = aws_security_group.app_sg.id
  source_security_group_id = aws_security_group.alb_sg.id
}

# App → RDS (ingress)
resource "aws_security_group_rule" "app_to_rds" {
  type                     = "ingress"
  from_port                = 3306
  to_port                  = 3306
  protocol                 = "tcp"
  security_group_id        = aws_security_group.rds_sg.id
  source_security_group_id = aws_security_group.app_sg.id
}
