#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_go_goroutines.h"

#include <stdlib.h>

/* True global resources - no need for thread safety here */
static int le_go_goroutines;

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(go_goroutines)
{
	// Initialize Go runtime
	InitGoRuntime();
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(go_goroutines)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(go_goroutines)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(go_goroutines)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(go_goroutines)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "Go Goroutines Support", "enabled");
	php_info_print_table_row(2, "Version", PHP_GO_GOROUTINES_VERSION);
	php_info_print_table_end();
}
/* }}} */

/* {{{ proto int go_start_goroutine()
   Start a simple goroutine and return its ID */
PHP_FUNCTION(go_start_goroutine)
{
	int id = StartGoroutine();
	RETURN_LONG(id);
}
/* }}} */

/* {{{ proto int go_start_goroutine_with_task(string task)
   Start a goroutine with a specific task description */
PHP_FUNCTION(go_start_goroutine_with_task)
{
	char *task = NULL;
	size_t task_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "s", &task, &task_len) == FAILURE) {
		RETURN_FALSE;
	}

	int id = StartGoroutineWithTask(task);
	RETURN_LONG(id);
}
/* }}} */

/* {{{ proto int go_check_status(int id)
   Check the status of a goroutine. Returns: -1 (not found), 0 (running), 1 (done) */
PHP_FUNCTION(go_check_status)
{
	zend_long id;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &id) == FAILURE) {
		RETURN_FALSE;
	}

	int status = CheckGoroutineStatus((int)id);
	RETURN_LONG(status);
}
/* }}} */

/* {{{ proto string go_get_result(int id)
   Get the result of a completed goroutine */
PHP_FUNCTION(go_get_result)
{
	zend_long id;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &id) == FAILURE) {
		RETURN_FALSE;
	}

	char* result = GetGoroutineResult((int)id);
	if (result == NULL) {
		RETURN_NULL();
	}

	// Copy the result to PHP's memory management
	zend_string *php_result = zend_string_init(result, strlen(result), 0);

	// Free the Go-allocated string
	FreeString(result);

	RETURN_STR(php_result);
}
/* }}} */

/* {{{ proto bool go_wait(int id, int timeout_ms)
   Wait for a goroutine to complete with timeout. Returns true if completed, false on timeout/error */
PHP_FUNCTION(go_wait)
{
	zend_long id;
	zend_long timeout_ms = 5000; // Default 5 seconds

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "l|l", &id, &timeout_ms) == FAILURE) {
		RETURN_FALSE;
	}

	int result = WaitForGoroutine((int)id, (int)timeout_ms);

	if (result == 1) {
		RETURN_TRUE;
	} else if (result == -2) {
		php_error_docref(NULL, E_WARNING, "Timeout waiting for goroutine %ld", id);
		RETURN_FALSE;
	} else {
		php_error_docref(NULL, E_WARNING, "Goroutine %ld not found", id);
		RETURN_FALSE;
	}
}
/* }}} */

/* {{{ proto void go_cleanup(int id)
   Clean up a goroutine's resources */
PHP_FUNCTION(go_cleanup)
{
	zend_long id;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &id) == FAILURE) {
		RETURN_NULL();
	}

	CleanupGoroutine((int)id);
	RETURN_NULL();
}
/* }}} */

/* {{{ proto string go_get_stats()
   Get statistics about goroutines */
PHP_FUNCTION(go_get_stats)
{
	char* stats = GetGoroutineStats();
	if (stats == NULL) {
		RETURN_NULL();
	}

	// Copy the result to PHP's memory management
	zend_string *php_stats = zend_string_init(stats, strlen(stats), 0);

	// Free the Go-allocated string
	FreeString(stats);

	RETURN_STR(php_stats);
}
/* }}} */

/* {{{ proto int go_start_delayed(int delay_ms)
   Start a goroutine that completes after a delay */
PHP_FUNCTION(go_start_delayed)
{
	zend_long delay_ms;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &delay_ms) == FAILURE) {
		RETURN_FALSE;
	}

	int id = StartGoroutineWithCallback((int)delay_ms);
	RETURN_LONG(id);
}
/* }}} */

/* {{{ proto int go_execute_php_code(string php_code)
   Execute PHP code in a goroutine */
PHP_FUNCTION(go_execute_php_code)
{
	char *code = NULL;
	size_t code_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "s", &code, &code_len) == FAILURE) {
		RETURN_FALSE;
	}

	int id = ExecutePHPCode(code);
	RETURN_LONG(id);
}
/* }}} */

/* {{{ proto int go_execute_php_file(string file_path)
   Execute a PHP file in a goroutine */
PHP_FUNCTION(go_execute_php_file)
{
	char *file_path = NULL;
	size_t file_path_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "s", &file_path, &file_path_len) == FAILURE) {
		RETURN_FALSE;
	}

	int id = ExecutePHPFile(file_path);
	RETURN_LONG(id);
}
/* }}} */

/* {{{ proto int go_execute_php_function(string function_call)
   Execute a PHP function call in a goroutine */
PHP_FUNCTION(go_execute_php_function)
{
	char *function_call = NULL;
	size_t function_call_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "s", &function_call, &function_call_len) == FAILURE) {
		RETURN_FALSE;
	}

	int id = ExecutePHPFunction(function_call);
	RETURN_LONG(id);
}
/* }}} */

/* {{{ proto void go_cleanup_temp_files()
   Clean up temporary PHP execution files */
PHP_FUNCTION(go_cleanup_temp_files)
{
	CleanupTempFiles();
	RETURN_NULL();
}
/* }}} */

/* {{{ go_goroutines_functions[]
 */
const zend_function_entry go_goroutines_functions[] = {
	PHP_FE(go_start_goroutine, NULL)
	PHP_FE(go_start_goroutine_with_task, NULL)
	PHP_FE(go_execute_php_code, NULL)
	PHP_FE(go_execute_php_file, NULL)
	PHP_FE(go_execute_php_function, NULL)
	PHP_FE(go_check_status, NULL)
	PHP_FE(go_get_result, NULL)
	PHP_FE(go_wait, NULL)
	PHP_FE(go_cleanup, NULL)
	PHP_FE(go_cleanup_temp_files, NULL)
	PHP_FE(go_get_stats, NULL)
	PHP_FE(go_start_delayed, NULL)
	PHP_FE_END
};
/* }}} */

/* {{{ go_goroutines_module_entry
 */
zend_module_entry go_goroutines_module_entry = {
	STANDARD_MODULE_HEADER,
	"go_goroutines",
	go_goroutines_functions,
	PHP_MINIT(go_goroutines),
	PHP_MSHUTDOWN(go_goroutines),
	PHP_RINIT(go_goroutines),
	PHP_RSHUTDOWN(go_goroutines),
	PHP_MINFO(go_goroutines),
	PHP_GO_GOROUTINES_VERSION,
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_GO_GOROUTINES
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(go_goroutines)
#endif
