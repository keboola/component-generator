devtools::load_all('/code/')
library(my.component)
app <- CustomApplicationExample$new(Sys.getenv("KBC_DATADIR"))
app$run()
