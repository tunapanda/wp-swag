#!/usr/bin/env bash

rm -rf site

echo "Running apigen..."
apigen generate -q

echo "Running mkdocs..."
mkdocs gh-deploy --dirty --force
