terraform {
  required_version = ">= 1.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = var.aws_region # Dynamic ดึงค่าจาก variables.tf
}

# 1. Security Group
resource "aws_security_group" "devops_sg" {
  name        = "differential-server-tf"
  description = "Security group for lovelypet web server"

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 8080
    to_port     = 8080
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 3000
    to_port     = 3000
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 9091
    to_port     = 9091
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
    Name = "differential-server-sg"
  }
}

# 2. Ubuntu 22.04 LTS AMI Lookup (Dynamic)
data "aws_ami" "ubuntu" {
  most_recent = true
  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-jammy-22.04-amd64-server-*"]
  }
  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }
  owners = ["099720109477"] # Canonical
}

# 3. EC2 Instance
resource "aws_instance" "devops_server" {
  ami                    = data.aws_ami.ubuntu.id # Dynamic
  instance_type          = var.instance_type      # Dynamic
  key_name               = var.key_name           # Dynamic
  vpc_security_group_ids = [aws_security_group.devops_sg.id] # Dynamic

  root_block_device {
    volume_size = 30
    volume_type = "gp3"
  }

  tags = {
    Name = "differential-server-tf"
  }
}

# 4. Elastic IP
resource "aws_eip" "devops_eip" {
  instance = aws_instance.devops_server.id # Dynamic
  domain   = "vpc"

  tags = {
    Name = "differential-devops-eip"
  }
}