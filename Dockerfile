# Use official Nginx image
FROM nginx:alpine

# Copy static files to Nginx default directory
COPY . /usr/share/nginx/html

# Remove default Nginx configuration
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Expose port 8069
EXPOSE 8069

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]