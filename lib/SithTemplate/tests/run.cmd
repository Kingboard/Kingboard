@echo off
setlocal
:: by PiotrLegnica
:: Revision: $Id$
:: This file is part of SithTemplate toolset.
:: SithTemplate is released under terms of the New BSD License.

:: this is NT batchfile

::
:: See ./environment/README to instructions on how to prepare testing environment.
::
:: Test outputs are put into ./out directory
::

set STDPATH=%SystemRoot%;%SystemRoot%\system32
set ENVPATH=%~dp0env
set PHPBASE=%envpath%\php
set PHPUNIT=%envpath%\PHPUnit\TextUI\Command.php

if not exist .\out (
 mkdir .\out
)

if not exist %ENVPATH% (
 echo :: Failure: %ENVPATH% does not exist
 endlocal
 exit /B 1
)

echo :: SithTemplate testsuite

set OUTPUT=console
set COVERAGE=no

:: Parse commandline
:nextVar
if "x%1" == "x-c" set COVERAGE=yes
if "x%1" == "x-l" set OUTPUT=logfile
if "x%1" == "x" goto finishVars
shift
goto nextVar

:finishVars

echo :: Reading run-config

:: Read run-config and run defined tests
for /f "skip=5" %%i in (.\run-config) do (
 if "x%%i" == "xcoverage" (
  if "%COVERAGE%" == "yes" (
   echo :: Running code coverage test
   call :runcoverage
  ) else (
   echo :: Skipping code coverage test
  )
 ) else (
  echo :: Running testsuite using '%%i' configuration
  echo ::: Output: %OUTPUT%
  if "%OUTPUT%" == "console" call :runtests %%i con
  if "%OUTPUT%" == "logfile" call :runtests %%i .\out\testerror-php%%i.log
 )
)

echo :: Finished
endlocal
exit /B 0

:: Subroutines

:runtests
path %PHPBASE%%1;%STDPATH%
set PHPBIN=%PHPBASE%%1\php.exe
set PHPRC=%PHPBASE%%1
if not exist %PHPBIN% (
 echo :: %PHPBIN% could not be found
 endlocal
 exit /B 1
)
%PHPBIN% -d safe_mode=off -d log_errors=1 -d error_log="./out/error-php%1.log" -d include_path=".;./env" ^
%PHPUNIT% -d display_errors=0 --log-xml "./out/testlog-php%1.xml" TemplateTestsEx > %2
exit /B %ERRORLEVEL%

:runcoverage
path %PHPBASE%52;%STDPATH%
set PHPBIN=%PHPBASE%52\php.exe
set PHPRC=%PHPBASE%52
if not exist %PHPBIN% (
 echo :: %PHPBIN% could not be found
 endlocal
 exit /B 1
)
%PHPBIN% -d safe_mode=off -d log_errors=1 -d error_log="./out/error-phpcoverage.log" -d include_path=".;./env" ^
%PHPUNIT% -d display_errors=0 --coverage-html "./out/coverage" TemplateTestsEx > .\out\testerror-phpcoverage.log
exit /B %ERRORLEVEL%