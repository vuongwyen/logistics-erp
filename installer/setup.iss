[Setup]
AppName=NT Logistics ERP
AppVersion=3.0.0
DefaultDirName=C:\NT-Logistics-ERP
DefaultGroupName=NT Logistics ERP
OutputBaseFilename=NT-Logistics-ERP-Setup-v3.0.0
Compression=lzma2/ultra
SolidCompression=yes
PrivilegesRequired=admin
OutputDir=..\

[Files]
Source: "..\*"; DestDir: "{app}"; Excludes: ".git\*,.idea\*,node_modules\*,vendor\*,server\*,tunnel.log,wan-url.txt,build.ps1,NT-Logistics-ERP-Setup*.exe"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "..\installer\prerequisites\*"; DestDir: "{app}\installer\prerequisites"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{commondesktop}\Khoi dong NT Logistics ERP"; Filename: "{app}\installer\start.bat"; WorkingDir: "{app}\installer"; IconFilename: "cmd.exe"

[Run]
Filename: "{app}\installer\install.bat"; Description: "Tien hanh thiet lap he thong tu dong (Bat buoc)"; Flags: postinstall runascurrentuser shellexec waituntilterminated
