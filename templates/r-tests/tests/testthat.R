library(testthat)

KBC_DATADIR <- '/data/'

# override with config if any
if (file.exists("config.R")) {
  source("config.R")
}

# override with environment if any
if (nchar(Sys.getenv("KBC_DATADIR")) > 0) {
  KBC_DATADIR <- Sys.getenv("KBC_DATADIR")
}

test_check("keboola.r.custom.application.subclass")
