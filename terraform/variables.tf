variable "aws_region" {
  description = "AWS Region"
  type        = string
  default     = "ap-southeast-2" # Sydney
}

variable "instance_type" {
  description = "EC2 Instance Type"
  type        = string
  default     = "t3.micro"
}

variable "key_name" {
  description = "Name of existing EC2 Key Pair for SSH"
  type        = string
  default     = "differential-key" # ชื่อ Key Pair ของพี่ดิฟ
}