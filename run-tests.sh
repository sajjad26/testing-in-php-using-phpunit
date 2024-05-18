#!/bin/bash

# Build the Docker image
docker build -t phpunit-test .
docker run -v $(pwd):/app phpunit-test