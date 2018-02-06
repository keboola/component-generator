#!/bin/bash
set -e

# deploy to Quay public repository
docker login -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io
docker tag component-generator quay.io/keboola/component-generator:${TRAVIS_TAG}
docker tag component-generator quay.io/keboola/component-generator:latest
docker images
docker push quay.io/keboola/component-generator:${TRAVIS_TAG}
docker push quay.io/keboola/component-generator:latest
