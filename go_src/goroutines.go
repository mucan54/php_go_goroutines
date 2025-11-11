package main

/*
#include <stdlib.h>
#include <string.h>

typedef struct {
    char* result;
    int done;
    void* error;
} GoRoutineResult;
*/
import "C"
import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"io/ioutil"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"sync"
	"time"
	"unsafe"
)

// Global storage for goroutine results
var (
	results   = make(map[int]*GoRoutineResult)
	resultsMu sync.RWMutex
	nextID    = 0
	idMu      sync.Mutex
	tempDir   string
)

// GoRoutineResult holds the result of a goroutine execution
type GoRoutineResult struct {
	Result string
	Done   bool
	Error  error
}

// getNextID returns a unique ID for a new goroutine
func getNextID() int {
	idMu.Lock()
	defer idMu.Unlock()
	id := nextID
	nextID++
	return id
}

//export InitGoRuntime
func InitGoRuntime() {
	// Initialize Go runtime with multiple threads for goroutines
	runtime.GOMAXPROCS(runtime.NumCPU())

	// Create temp directory for PHP execution
	tempDir = filepath.Join(os.TempDir(), "go_goroutines_php")
	os.MkdirAll(tempDir, 0755)
}

//export StartGoroutine
func StartGoroutine() C.int {
	id := getNextID()

	result := &GoRoutineResult{
		Done: false,
	}

	resultsMu.Lock()
	results[id] = result
	resultsMu.Unlock()

	go func() {
		defer func() {
			if r := recover(); r != nil {
				result.Error = fmt.Errorf("panic: %v", r)
				result.Done = true
			}
		}()

		// Simulate some work
		time.Sleep(100 * time.Millisecond)
		result.Result = "Goroutine completed successfully!"
		result.Done = true
	}()

	return C.int(id)
}

//export StartGoroutineWithTask
func StartGoroutineWithTask(task *C.char) C.int {
	id := getNextID()
	taskStr := C.GoString(task)

	result := &GoRoutineResult{
		Done: false,
	}

	resultsMu.Lock()
	results[id] = result
	resultsMu.Unlock()

	go func() {
		defer func() {
			if r := recover(); r != nil {
				result.Error = fmt.Errorf("panic: %v", r)
				result.Done = true
			}
		}()

		// Simulate task execution
		time.Sleep(200 * time.Millisecond)
		result.Result = fmt.Sprintf("Task '%s' completed!", taskStr)
		result.Done = true
	}()

	return C.int(id)
}

//export ExecutePHPCode
func ExecutePHPCode(phpCode *C.char) C.int {
	id := getNextID()
	code := C.GoString(phpCode)

	result := &GoRoutineResult{
		Done: false,
	}

	resultsMu.Lock()
	results[id] = result
	resultsMu.Unlock()

	go func() {
		defer func() {
			if r := recover(); r != nil {
				result.Error = fmt.Errorf("panic: %v", r)
				result.Done = true
			}
		}()

		// Execute PHP code
		output, err := executePHP(code)
		if err != nil {
			result.Error = err
			result.Result = fmt.Sprintf("Error: %v", err)
		} else {
			result.Result = output
		}
		result.Done = true
	}()

	return C.int(id)
}

//export ExecutePHPFile
func ExecutePHPFile(phpFilePath *C.char) C.int {
	id := getNextID()
	filePath := C.GoString(phpFilePath)

	result := &GoRoutineResult{
		Done: false,
	}

	resultsMu.Lock()
	results[id] = result
	resultsMu.Unlock()

	go func() {
		defer func() {
			if r := recover(); r != nil {
				result.Error = fmt.Errorf("panic: %v", r)
				result.Done = true
			}
		}()

		// Execute PHP file
		output, err := executePHPFile(filePath)
		if err != nil {
			result.Error = err
			result.Result = fmt.Sprintf("Error: %v", err)
		} else {
			result.Result = output
		}
		result.Done = true
	}()

	return C.int(id)
}

//export ExecutePHPFunction
func ExecutePHPFunction(functionCall *C.char) C.int {
	id := getNextID()
	funcCall := C.GoString(functionCall)

	result := &GoRoutineResult{
		Done: false,
	}

	resultsMu.Lock()
	results[id] = result
	resultsMu.Unlock()

	go func() {
		defer func() {
			if r := recover(); r != nil {
				result.Error = fmt.Errorf("panic: %v", r)
				result.Done = true
			}
		}()

		// Build PHP code to call function
		phpCode := fmt.Sprintf("<?php\n%s\n?>", funcCall)

		output, err := executePHP(phpCode)
		if err != nil {
			result.Error = err
			result.Result = fmt.Sprintf("Error: %v", err)
		} else {
			result.Result = output
		}
		result.Done = true
	}()

	return C.int(id)
}

// executePHP executes PHP code and returns output
func executePHP(code string) (string, error) {
	// Create temp file with unique name
	hash := md5.Sum([]byte(code + time.Now().String()))
	filename := hex.EncodeToString(hash[:]) + ".php"
	tmpFile := filepath.Join(tempDir, filename)

	// Write PHP code to temp file
	if err := ioutil.WriteFile(tmpFile, []byte(code), 0644); err != nil {
		return "", fmt.Errorf("failed to write temp file: %v", err)
	}
	defer os.Remove(tmpFile)

	// Execute PHP
	cmd := exec.Command("php", tmpFile)
	output, err := cmd.CombinedOutput()
	if err != nil {
		return string(output), fmt.Errorf("PHP execution failed: %v", err)
	}

	return string(output), nil
}

// executePHPFile executes a PHP file and returns output
func executePHPFile(filePath string) (string, error) {
	// Check if file exists
	if _, err := os.Stat(filePath); os.IsNotExist(err) {
		return "", fmt.Errorf("file not found: %s", filePath)
	}

	// Execute PHP file
	cmd := exec.Command("php", filePath)
	output, err := cmd.CombinedOutput()
	if err != nil {
		return string(output), fmt.Errorf("PHP execution failed: %v", err)
	}

	return string(output), nil
}

//export CheckGoroutineStatus
func CheckGoroutineStatus(id C.int) C.int {
	resultsMu.RLock()
	defer resultsMu.RUnlock()

	result, exists := results[int(id)]
	if !exists {
		return -1 // Not found
	}

	if result.Done {
		return 1 // Done
	}
	return 0 // Still running
}

//export GetGoroutineResult
func GetGoroutineResult(id C.int) *C.char {
	resultsMu.RLock()
	defer resultsMu.RUnlock()

	result, exists := results[int(id)]
	if !exists {
		return C.CString("Error: Goroutine not found")
	}

	if !result.Done {
		return C.CString("Error: Goroutine still running")
	}

	if result.Error != nil {
		return C.CString(fmt.Sprintf("Error: %v", result.Error))
	}

	return C.CString(result.Result)
}

//export WaitForGoroutine
func WaitForGoroutine(id C.int, timeoutMs C.int) C.int {
	timeout := time.Duration(timeoutMs) * time.Millisecond
	start := time.Now()

	for {
		status := CheckGoroutineStatus(id)
		if status == 1 || status == -1 {
			return status
		}

		if time.Since(start) > timeout {
			return -2 // Timeout
		}

		time.Sleep(10 * time.Millisecond)
	}
}

//export CleanupGoroutine
func CleanupGoroutine(id C.int) {
	resultsMu.Lock()
	defer resultsMu.Unlock()
	delete(results, int(id))
}

//export GetActiveGoroutineCount
func GetActiveGoroutineCount() C.int {
	return C.int(runtime.NumGoroutine())
}

//export GetGoroutineStats
func GetGoroutineStats() *C.char {
	resultsMu.RLock()
	defer resultsMu.RUnlock()

	active := 0
	completed := 0
	failed := 0

	for _, result := range results {
		if result.Done {
			if result.Error != nil {
				failed++
			} else {
				completed++
			}
		} else {
			active++
		}
	}

	stats := fmt.Sprintf("Total: %d, Active: %d, Completed: %d, Failed: %d, Go Routines: %d",
		len(results), active, completed, failed, runtime.NumGoroutine())

	return C.CString(stats)
}

//export StartGoroutineWithCallback
func StartGoroutineWithCallback(sleepMs C.int) C.int {
	id := getNextID()

	result := &GoRoutineResult{
		Done: false,
	}

	resultsMu.Lock()
	results[id] = result
	resultsMu.Unlock()

	go func() {
		defer func() {
			if r := recover(); r != nil {
				result.Error = fmt.Errorf("panic: %v", r)
				result.Done = true
			}
		}()

		// Sleep for specified duration
		time.Sleep(time.Duration(sleepMs) * time.Millisecond)
		result.Result = fmt.Sprintf("Callback executed after %dms", sleepMs)
		result.Done = true
	}()

	return C.int(id)
}

//export CleanupTempFiles
func CleanupTempFiles() {
	if tempDir != "" {
		os.RemoveAll(tempDir)
		os.MkdirAll(tempDir, 0755)
	}
}

//export FreeString
func FreeString(s *C.char) {
	C.free(unsafe.Pointer(s))
}

func main() {
	// This is required for building as a shared library
}
