# ==========================================
# RDS Subnet Group
# ==========================================
resource "aws_db_subnet_group" "easybank" {
  name       = "easybank-db-subnet-group"
  subnet_ids = aws_subnet.data[*].id

  tags = {
    Name = "easybank-db-subnet-group"
  }
}

# ==========================================
# RDS Instance
# ==========================================
resource "aws_db_instance" "easybank" {
  engine                 = "mariadb"
  instance_class         = "db.t3.micro"
  allocated_storage      = 20
  db_name                = "easybank"
  username               = var.db_username
  password               = var.db_password
  multi_az               = true
  db_subnet_group_name   = aws_db_subnet_group.easybank.name
  vpc_security_group_ids = [aws_security_group.rds_sg.id]
  skip_final_snapshot    = true
}