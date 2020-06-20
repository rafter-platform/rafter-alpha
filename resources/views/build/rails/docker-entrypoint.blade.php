#!/bin/bash
set -e

bundle exec puma -t 5:5 -p ${PORT:-3000} -e ${RAILS_ENV:-development}
