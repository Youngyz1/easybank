# ==========================================
# Availability Zones
# ==========================================
data "aws_availability_zones" "azs" {}

# ==========================================
# VPC
# ==========================================
resource "aws_vpc" "easybank" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_support   = true
  enable_dns_hostnames = true

  tags = {
    Name = "easybank-vpc"
  }
}

# ==========================================
# Subnets
# ==========================================
# Public subnets
resource "aws_subnet" "public" {
  count                   = 2
  vpc_id                  = aws_vpc.easybank.id
  cidr_block              = "10.0.${count.index + 1}.0/24"
  map_public_ip_on_launch = true
  availability_zone       = data.aws_availability_zones.azs.names[count.index]

  tags = {
    Name = "easybank-public-${count.index + 1}"
  }
}

# Private / App subnets
resource "aws_subnet" "app" {
  count             = 2
  vpc_id            = aws_vpc.easybank.id
  cidr_block        = "10.0.${count.index + 10}.0/24"
  availability_zone = data.aws_availability_zones.azs.names[count.index]

  tags = {
    Name = "easybank-app-${count.index + 1}"
  }
}

# Data / RDS subnets
resource "aws_subnet" "data" {
  count             = 2
  vpc_id            = aws_vpc.easybank.id
  cidr_block        = "10.0.${count.index + 20}.0/24"
  availability_zone = data.aws_availability_zones.azs.names[count.index]

  tags = {
    Name = "easybank-data-${count.index + 1}"
  }
}

# ==========================================
# Internet Gateway
# ==========================================
resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.easybank.id

  tags = {
    Name = "easybank-igw"
  }
}

# ==========================================
# Elastic IPs for NAT
# ==========================================
resource "aws_eip" "nat" {
  count  = length(aws_subnet.public)
  domain = "vpc"
}

# ==========================================
# NAT Gateways (one per public subnet)
# ==========================================
resource "aws_nat_gateway" "nat" {
  for_each = {for i, s in aws_subnet.public : i => s}

  allocation_id = aws_eip.nat[each.key].id
  subnet_id     = each.value.id

  tags = {
    Name = "easybank-nat-${each.key + 1}"
  }
}

# ==========================================
# VPC Endpoints for ECR (private access)
# ==========================================
resource "aws_vpc_endpoint" "ecr_api" {
  vpc_id            = aws_vpc.easybank.id
  service_name      = "com.amazonaws.us-east-1.ecr.api"
  vpc_endpoint_type = "Interface"
  subnet_ids        = aws_subnet.app[*].id
  security_group_ids = [aws_security_group.app_sg.id]
}

resource "aws_vpc_endpoint" "ecr_dkr" {
  vpc_id            = aws_vpc.easybank.id
  service_name      = "com.amazonaws.us-east-1.ecr.dkr"
  vpc_endpoint_type = "Interface"
  subnet_ids        = aws_subnet.app[*].id
  security_group_ids = [aws_security_group.app_sg.id]
}
