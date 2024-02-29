FROM nginx:1-alpine

WORKDIR /app

COPY nginx/nginx.conf /etc/nginx/nginx.conf
COPY nginx/includes /etc/nginx/includes
COPY nginx/upstreams /etc/nginx/conf.d
COPY nginx/vhosts /etc/nginx/conf.d

EXPOSE 443

STOPSIGNAL SIGTERM

CMD ["nginx", "-g", "daemon off;"]
