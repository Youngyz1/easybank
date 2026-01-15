resource "aws_ecr_repository" "easybank" {
  name                 = "easybank"
  image_tag_mutability = "MUTABLE"
  force_delete         = true # removes all images before deletion

  image_scanning_configuration {
    scan_on_push = true
  }

  encryption_configuration {
    encryption_type = "AES256"
  }

  tags = {
    Name = "easybank-ecr"
  }
}
