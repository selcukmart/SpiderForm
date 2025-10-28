#!/bin/bash

# Generate code coverage report for FormGenerator V2

echo "Generating code coverage report..."
echo "===================================="

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/html --coverage-text --coverage-clover coverage/clover.xml

echo ""
echo "Coverage reports generated:"
echo "- HTML: coverage/html/index.html"
echo "- Text: coverage/coverage.txt"
echo "- Clover XML: coverage/clover.xml"
echo ""
echo "Open coverage/html/index.html in your browser to view the report."
