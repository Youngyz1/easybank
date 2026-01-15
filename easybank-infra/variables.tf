# ==========================================
# AWS Region
# ==========================================
variable "aws_region" {
  description = "AWS region where EasyBank resources will be deployed"
  type        = string
  default     = "us-east-1"
}

# ==========================================
# VPC Configuration
# ==========================================
variable "vpc_cidr" {
  description = "CIDR block for the EasyBank VPC"
  type        = string
  default     = "10.0.0.0/16"
}

variable "azs" {
  description = "List of Availability Zones to use for subnets"
  type        = list(string)
  default     = ["us-east-1a", "us-east-1b"]
}

# ==========================================
# RDS Database Configuration
# ==========================================
variable "db_password" {
  description = "RDS database password (sensitive)"
  type        = string
  sensitive   = true
}

# Optional: database username
variable "db_username" {
  description = "RDS database master username"
  type        = string
  default     = "easybank_admin"
}

# ==========================================
# ECS / Docker Image
# ==========================================
variable "easybank_image" {
  description = "Full ECS Docker image URI (Git SHA tagged)"
  type        = string
  default     = "public.ecr.aws/nginx/nginx:latest"
}


# ==========================================
# Optional: ECS cluster and service names
# ==========================================
variable "ecs_cluster_name" {
  description = "ECS Cluster name for EasyBank"
  type        = string
  default     = "easybank-cluster"
}

variable "ecs_service_name" {
  description = "ECS Service name for EasyBank"
  type        = string
  default     = "easybank-service"
}

# ==========================================
# ECS input variables
# ==========================================


variable "lb_target_group_arn" {
  description = "ARN of the existing ALB target group"
  type        = string
}
