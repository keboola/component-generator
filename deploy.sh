#!/bin/bash
set -e

GITHUB_TAG=${GITHUB_REF/refs\/tags\//}

# deploy to Quay public repository
docker login -u="${QUAY_USERNAME}" -p="${QUAY_PASSWORD}" quay.io
docker tag component-generator quay.io/${APP_IMAGE}:${GITHUB_TAG}
docker tag component-generator quay.io/${APP_IMAGE}:latest
docker images
docker push quay.io/${APP_IMAGE}:${GITHUB_TAG}
docker push quay.io/${APP_IMAGE}:latest
