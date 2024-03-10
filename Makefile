#!/usr/bin/make

SHELL = /bin/sh

UID := $(shell id -u)
GID := $(shell id -g)
USER:= $(shell whoami)

export UID
export GID
export USER

up:
	docker-compose up -d

create_code_zip:
	cd /var/lib/jenkins/workspace/UniquoTest
    rm -rf artifact.zip
    zip -r artifact.zip . -x "*node_modules**"