FROM node:18-alpine AS build

WORKDIR /app

COPY . .

RUN mkdir -p dist && \

    cp -r *.php dist/ 2>/dev/null || true && \

    cp -r *.css dist/ 2>/dev/null || true && \

    cp -r *.js dist/ 2>/dev/null || true && \

    cp nginx.conf dist/ 2>/dev/null || true && \

    cp .htaccess dist/ 2>/dev/null || true && \

    cp guest.png dist/ 2>/dev/null || true && \

    mkdir -p dist/uploads

RUN echo "=== Sadržaj dist/ foldera ===" && ls -la dist/

FROM nginx:alpine AS serve

RUN apk add --no-cache curl

RUN rm -rf /usr/share/nginx/html/*

COPY --from=build /app/dist /var/www/filmoteka

COPY nginx.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/filmoteka/uploads && \
    chmod -R 755 /var/www/filmoteka && \
    chmod -R 775 /var/www/filmoteka/uploads

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
