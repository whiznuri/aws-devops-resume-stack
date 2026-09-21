output "public_ip" {
  description = "Elastic Public IP of the EC2 Instance"
  value       = aws_eip.devops_eip.public_ip
}

output "ssh_command" {
  description = "Command to SSH into the instance"
  value       = "ssh -i C:/Users/User/Downloads/.ssh_key/${var.key_name}.pem ubuntu@${aws_eip.devops_eip.public_ip}"
}