dnl config.m4 for extension go_goroutines

PHP_ARG_ENABLE(go_goroutines, whether to enable Go goroutines support,
[  --enable-go-goroutines       Enable Go goroutines support])

if test "$PHP_GO_GOROUTINES" != "no"; then
  dnl Check for Go compiler
  AC_PATH_PROG(GO, go, no)
  if test "$GO" = "no"; then
    AC_MSG_ERROR([Go compiler not found. Please install Go 1.18 or higher.])
  fi

  dnl Check Go version
  GO_VERSION=`$GO version | awk '{print $3}' | sed 's/go//'`
  AC_MSG_CHECKING([Go version])
  AC_MSG_RESULT([$GO_VERSION])

  dnl Build the Go shared library
  AC_MSG_CHECKING([for Go shared library])

  dnl Set up Go build
  GO_SRC_DIR="go_src"
  GO_LIB_NAME="libgoroutines.so"
  GO_LIB_PATH="$GO_SRC_DIR/$GO_LIB_NAME"

  dnl Build Go library during configure
  AC_MSG_NOTICE([Building Go shared library...])
  cd $GO_SRC_DIR && $GO build -buildmode=c-shared -o $GO_LIB_NAME goroutines.go && cd ..

  if test ! -f "$GO_LIB_PATH"; then
    AC_MSG_ERROR([Failed to build Go shared library])
  fi

  AC_MSG_RESULT([yes])

  dnl Add the Go library to the build
  PHP_ADD_LIBRARY_WITH_PATH(goroutines, $GO_SRC_DIR, GO_GOROUTINES_SHARED_LIBADD)

  dnl Add pthread support (required by Go runtime)
  PHP_ADD_LIBRARY(pthread, 1, GO_GOROUTINES_SHARED_LIBADD)

  PHP_SUBST(GO_GOROUTINES_SHARED_LIBADD)

  dnl Add source files
  PHP_NEW_EXTENSION(go_goroutines, php_go_goroutines.c, $ext_shared)
fi
