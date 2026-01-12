# ==========================================
# AWS region
# ==========================================
variable "aws_region" {
  description = "AWS region to deploy EasyBank"
  type        = string
  default     = "us-east-1"
}

# ==========================================
# VPC CIDR block
# ==========================================
variable "vpc_cidr" {
  description = "CIDR block for the EasyBank VPC"
  type        = string
  default     = "10.0.0.0/16"
}

# ==========================================
# Availability Zones
# ==========================================
variable "azs" {
  description = "List of Availability Zones to use"
  type        = list(string)
  default     = ["us-east-1a", "us-east-1b"]
}

# ==========================================
# RDS Database password
# ==========================================
variable "db_password" {
  description = "RDS database password (sensitive)"
  type        = string
  sensitive   = true
}

# ==========================================
# ECS Docker image
# ==========================================
variable "easybank_image" {
  description = "ECS Docker image for EasyBank (Git SHA tagged)"
  type        = string
  default     = "958421185668.dkr.ecr.us-east-1.amazonaws.com/easybank:${var.image_tag}"
}

# Optional: pass image tag dynamically (from CI/CD)
variable "image_tag" {
  description = "Docker image tag (usually Git SHA)"
  type        = string
  default     = "latest"  # CI/CD will override with Git SHA
}
