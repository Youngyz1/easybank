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
# ECS Docker image (full URI, Git SHA tagged)
# ==========================================
variable "easybank_image" {
  description = "Full ECS Docker image URI (Git SHA tagged)"
  type        = string
  sensitive   = false
}

# ==========================================
# Optional: pass image tag dynamically (CI/CD)
# ==========================================
variable "image_tag" {
  description = "Docker image tag (usually Git SHA). Can be overridden by CI/CD."
  type        = string
  default     = "latest"
}
