#ifndef PHP_GO_GOROUTINES_H
#define PHP_GO_GOROUTINES_H

extern zend_module_entry go_goroutines_module_entry;
#define phpext_go_goroutines_ptr &go_goroutines_module_entry

#define PHP_GO_GOROUTINES_VERSION "0.1.0"

#ifdef PHP_WIN32
#	define PHP_GO_GOROUTINES_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#	define PHP_GO_GOROUTINES_API __attribute__ ((visibility("default")))
#else
#	define PHP_GO_GOROUTINES_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

// Declare Go functions
extern int InitGoRuntime();
extern int StartGoroutine();
extern int StartGoroutineWithTask(char* task);
extern int ExecutePHPCode(char* phpCode);
extern int ExecutePHPFile(char* phpFilePath);
extern int ExecutePHPFunction(char* functionCall);
extern int CheckGoroutineStatus(int id);
extern char* GetGoroutineResult(int id);
extern int WaitForGoroutine(int id, int timeoutMs);
extern void CleanupGoroutine(int id);
extern int GetActiveGoroutineCount();
extern char* GetGoroutineStats();
extern int StartGoroutineWithCallback(int sleepMs);
extern void CleanupTempFiles();
extern void FreeString(char* s);

// PHP function declarations
PHP_MINIT_FUNCTION(go_goroutines);
PHP_MSHUTDOWN_FUNCTION(go_goroutines);
PHP_RINIT_FUNCTION(go_goroutines);
PHP_RSHUTDOWN_FUNCTION(go_goroutines);
PHP_MINFO_FUNCTION(go_goroutines);

PHP_FUNCTION(go_start_goroutine);
PHP_FUNCTION(go_start_goroutine_with_task);
PHP_FUNCTION(go_execute_php_code);
PHP_FUNCTION(go_execute_php_file);
PHP_FUNCTION(go_execute_php_function);
PHP_FUNCTION(go_check_status);
PHP_FUNCTION(go_get_result);
PHP_FUNCTION(go_wait);
PHP_FUNCTION(go_cleanup);
PHP_FUNCTION(go_cleanup_temp_files);
PHP_FUNCTION(go_get_stats);
PHP_FUNCTION(go_start_delayed);

#endif	/* PHP_GO_GOROUTINES_H */
