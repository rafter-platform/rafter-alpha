# Use the official lightweight Node.js 12 image.
# https://hub.docker.com/_/node
FROM node:12-slim

# Create and change to the app directory.
WORKDIR /usr/src/app

# Copy application dependency manifests to the container image.
# A wildcard is used to ensure both package.json AND package-lock.json are copied.
# Copying this separately prevents re-running npm install on every code change.
COPY package*.json yarn*.lock ./

# Install production dependencies.
RUN yarn --only=production

# Copy local code to the container image.
COPY . ./

# Run a build script, if present
RUN yarn build 2>&1 || true

RUN chmod 755 docker-entrypoint.sh

ENTRYPOINT ["/usr/src/app/docker-entrypoint.sh"]
