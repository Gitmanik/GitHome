FROM docker.io/python:3.11.3-alpine

RUN apk add --update-cache py3-pip
RUN pip install requests
RUN pip install tinytuya

WORKDIR /app

COPY . .

ENTRYPOINT ["python3", "virtual.py"]