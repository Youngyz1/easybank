terraform {
  backend "s3" {
    bucket         = "easybank-terraform-state"
    key            = "easybank/terraform.tfstate"
    region         = "us-east-1"
    dynamodb_table = "easybank-terraform-locks"
    encrypt        = true
  }
}
