#!/bin/bash

# Build the Docker image
docker build -t phpunit-test .
docker run --rm -v $(pwd):/app phpunit-test