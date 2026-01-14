output "vpc_id" {
  value = aws_vpc.easybank.id
}

output "public_subnet_ids" {
  value = aws_subnet.public[*].id
}

output "private_subnet_ids" {
  value = aws_subnet.app[*].id
}

output "app_sg_id" {
  value = aws_security_group.app_sg.id
}

output "db_sg_id" {
  value = aws_security_group.rds_sg.id
}

output "alb_dns_name" {
  value = aws_lb.easybank.dns_name
}

output "db_endpoint" {
  value = aws_db_instance.easybank.endpoint
}

output "db_port" {
  value = aws_db_instance.easybank.port
}

output "easybank_ecr_url" {
  value = aws_ecr_repository.easybank.repository_url
}

