#!/bin/sh
set -e

R CMD build /code/
R CMD check --as-cran --no-manual /code/
