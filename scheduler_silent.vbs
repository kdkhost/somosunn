Set fso = CreateObject("Scripting.FileSystemObject")
Set WshShell = CreateObject("WScript.Shell")
strPath = fso.GetParentFolderName(WScript.ScriptFullName)
WshShell.Run chr(34) & strPath & "\run_scheduler.bat" & Chr(34), 0
Set WshShell = Nothing
