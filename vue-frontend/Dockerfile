# Etapa 1: build com Node
FROM node:18 AS build-stage

WORKDIR /app
COPY . .
RUN npm install
RUN npm run build

# Etapa 2: servidor Nginx para servir a app
FROM nginx:stable-alpine AS production-stage

COPY --from=build-stage /app/dist /usr/share/nginx/html
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
