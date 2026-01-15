# ==========================================
# Public Route Table
# ==========================================
resource "aws_route_table" "public" {
  vpc_id = aws_vpc.easybank.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.igw.id
  }

  tags = {
    Name = "easybank-public-rt"
  }
}

# Associate each public subnet with the public RT
resource "aws_route_table_association" "public" {
  count          = length(aws_subnet.public)
  subnet_id      = aws_subnet.public[count.index].id
  route_table_id = aws_route_table.public.id
}

# ==========================================
# Private / App Route Tables (one per private subnet)
# ==========================================
resource "aws_route_table" "private" {
  for_each = { for i, s in aws_subnet.app : i => s }

  vpc_id = aws_vpc.easybank.id

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.nat[each.key].id
  }

  tags = {
    Name = "easybank-private-rt-${each.key + 1}"
  }
}

# Associate private RT with private subnets
resource "aws_route_table_association" "private" {
  for_each       = { for i, s in aws_subnet.app : i => s }
  subnet_id      = each.value.id
  route_table_id = aws_route_table.private[each.key].id
}

# ==========================================
# Data / RDS Route Table
# ==========================================
resource "aws_route_table" "data" {
  vpc_id = aws_vpc.easybank.id

  tags = {
    Name = "easybank-data-rt"
  }
}

# Associate each RDS subnet with the Data RT
resource "aws_route_table_association" "data" {
  count          = length(aws_subnet.data)
  subnet_id      = aws_subnet.data[count.index].id
  route_table_id = aws_route_table.data.id
}
