#!/usr/bin/env bash
##
# Generate coverage report.
#
set -e
echo "==> Generate code coverage report"
ahoy cli "phpdbg -qrr vendor/bin/phpunit ./dpc-sdp --coverage-html /app/coverage-report"
