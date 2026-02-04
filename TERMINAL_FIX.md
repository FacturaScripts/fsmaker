# Terminal Compatibility Fix

## Problem
The `fsmaker controller` command was failing with the error:
```
stty: invalid argument '4500:5:f00bf:8a3b:3:1c:7f:15:4:0:1:0:11:13:1a:0:12:f:17:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0'
```

This occurred because Laravel Prompts was trying to use `stty` commands to manipulate terminal settings, but the terminal environment didn't fully support these operations.

## Root Cause
When Laravel Prompts runs in non-standard terminal environments (like certain IDEs, containers, or piped contexts), the `stty -g` command may:
1. Fail with "Inappropriate ioctl for device"
2. Return terminal settings in a format that can't be restored
3. Cause the command to crash when trying to restore terminal state

## Solution
Created a patched version of Laravel Prompts' Terminal class that:
1. Detects whether `stty` is available on the system (especially important for Windows)
2. Skips stty calls entirely if the command is not available
3. Catches `RuntimeException` errors from stty commands when they do run
4. Silently ignores stty failures instead of crashing
5. Allows prompts to continue working even when terminal manipulation is unavailable

### Windows Compatibility
The patch includes special handling for Windows:
- Detects Windows using `DIRECTORY_SEPARATOR`
- Checks if `stty` is available (Git Bash, WSL, Cygwin)
- If `stty` is not found, skips all terminal manipulation attempts
- Caches the availability check to avoid repeated system calls
- Works in cmd.exe, PowerShell, Git Bash, and WSL environments

## Files Modified

### 1. `src/Patches/TerminalPatch.php` (NEW)
A drop-in replacement for `Laravel\Prompts\Terminal` that wraps stty calls in try-catch blocks to handle errors gracefully.

### 2. `composer.json`
Added the patch file to the autoload files array:
```json
"autoload": {
  "psr-4": {
    "fsmaker\\": "src/"
  },
  "files": [
    "src/Patches/TerminalPatch.php"
  ]
}
```

## How It Works
1. Composer's files autoload loads `TerminalPatch.php` early in the autoload process
2. The patched `Terminal` class is defined in the `Laravel\Prompts` namespace
3. When Laravel Prompts tries to instantiate `Terminal`, it uses our patched version instead
4. stty errors are caught and ignored, preventing crashes
5. Prompts continue to function, just without advanced terminal features

## Testing
After applying the fix:
```bash
composer dump-autoload
./bin/fsmaker controller
```

The command now runs successfully without stty errors.

## Notes
- This is a runtime patch that doesn't modify vendor files
- The patch is transparent to the rest of the application
- If Laravel Prompts fixes this issue upstream, you can remove the patch
- The prompts still work; they just don't manipulate terminal state in incompatible environments
- **Windows Support**: Works on Windows (cmd.exe, PowerShell, Git Bash, WSL)
- The patch detects the operating system and adapts its behavior accordingly
- Performance: The stty availability check is cached after the first call
