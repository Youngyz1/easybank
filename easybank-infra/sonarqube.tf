# ==========================================
# SonarQube EC2 Instance
# ==========================================
resource "aws_instance" "sonarqube" {
  ami                    = "ami-0c02fb55956c7d316" # Amazon Linux 2 us-east-1
  instance_type          = "t3.medium"
  subnet_id              = aws_subnet.public[0].id
  vpc_security_group_ids = [aws_security_group.sonarqube_sg.id]

  user_data = <<-EOF
    #!/bin/bash
    yum update -y
    yum install -y docker
    systemctl start docker
    systemctl enable docker
    usermod -aG docker ec2-user

    # Add swap for SonarQube
    fallocate -l 4G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab

    # Run SonarQube
    docker run -d \
      --name sonarqube \
      --restart always \
      -p 9000:9000 \
      sonarqube:lts-community
  EOF

  tags = {
    Name = "easybank-sonarqube"
  }
}

# ==========================================
# SonarQube Security Group
# ==========================================
resource "aws_security_group" "sonarqube_sg" {
  name        = "easybank-sonarqube-sg"
  description = "Security group for SonarQube"
  vpc_id      = aws_vpc.easybank.id

  ingress {
    from_port   = 9000
    to_port     = 9000
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "easybank-sonarqube-sg"
  }
}