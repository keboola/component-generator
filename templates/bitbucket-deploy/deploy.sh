#!/bin/bash
set -e

## Obtain the application repository and log in
docker pull quay.io/keboola/developer-portal-cli-v2:latest
export REPOSITORY=`docker run --rm  \
    -e KBC_DEVELOPERPORTAL_USERNAME \
    -e KBC_DEVELOPERPORTAL_PASSWORD \
    quay.io/keboola/developer-portal-cli-v2:latest \
    ecr:get-repository ${KBC_DEVELOPERPORTAL_VENDOR} ${KBC_DEVELOPERPORTAL_APP}`
eval $(docker run --rm \
    -e KBC_DEVELOPERPORTAL_USERNAME \
    -e KBC_DEVELOPERPORTAL_PASSWORD \
    quay.io/keboola/developer-portal-cli-v2:latest \
    ecr:get-login ${KBC_DEVELOPERPORTAL_VENDOR} ${KBC_DEVELOPERPORTAL_APP})

# Push to the repository
docker tag ${APP_IMAGE}:latest ${REPOSITORY}:${BITBUCKET_TAG}
docker tag ${APP_IMAGE}:latest ${REPOSITORY}:latest
docker push ${REPOSITORY}:${BITBUCKET_TAG}
docker push ${REPOSITORY}:latest


if echo ${BITBUCKET_TAG} | grep -c '^[0-9]\+\.[0-9]\+\.[0-9]\+$'
then
    # Deploy to KBC -> update the tag in Keboola Developer Portal (needs $KBC_DEVELOPERPORTAL_VENDOR & $KBC_DEVELOPERPORTAL_APP)
    docker run --rm \
        -e KBC_DEVELOPERPORTAL_USERNAME \
        -e KBC_DEVELOPERPORTAL_PASSWORD \
        quay.io/keboola/developer-portal-cli-v2:latest \
        update-app-repository ${KBC_DEVELOPERPORTAL_VENDOR} ${KBC_DEVELOPERPORTAL_APP} ${BITBUCKET_TAG} ecr ${REPOSITORY}
else
    echo "Skipping deployment to KBC, tag ${BITBUCKET_TAG} is not allowed."
fi
