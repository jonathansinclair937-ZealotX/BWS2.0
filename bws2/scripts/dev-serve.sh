#!/usr/bin/env bash
php -S localhost:8000 -t "$(dirname "$0")/../apps/web"
