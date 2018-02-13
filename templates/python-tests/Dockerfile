FROM python:3.6-alpine
RUN apk add --no-cache git \
	&& pip3 install --no-cache-dir --upgrade pytest flake8 \
	&& pip3 install --no-cache-dir --upgrade --force-reinstall git+git://github.com/keboola/python-docker-application.git@2.0.1

WORKDIR /code

COPY . /code/

# Run the application
CMD python3 -u ./src/main.py
