# cifrado-web
Cifrado static web page.

## Docker Deployment

### Prerequisites
- Docker installed
- Docker Compose installed

### Local Development
1. Clone this repository
2. Run: `docker-compose up -d`
3. Access the site at: http://localhost

### Ubuntu Server Deployment
1. SSH into your Ubuntu server
2. Install Docker and Docker Compose:
   ```bash
   sudo apt update
   sudo apt install docker.io docker-compose
   sudo systemctl enable docker
   sudo systemctl start docker
   ```
3. Clone this repository:
   ```bash
   git clone https://github.com/your-repo/cifrado-web.git
   cd cifrado-web
   ```
4. Build and start the container:
   ```bash
   docker-compose up -d --build
   ```
5. Configure firewall (if needed):
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw reload
   ```
6. Access your site at: http://your-server-ip

### Maintenance Commands
- Stop the container: `docker-compose down`
- View logs: `docker-compose logs -f`
- Restart the container: `docker-compose restart`
- Update the deployment:
  ```bash
  git pull origin main
  docker-compose up -d --build
