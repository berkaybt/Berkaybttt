[Setup]
AppName=Hotel IT Suite
AppVersion=0.1.0
DefaultDirName={pf}\HotelITSuite
DefaultGroupName=Hotel IT Suite
OutputBaseFilename=HotelITSuiteSetup
Compression=lzma
SolidCompression=yes

[Files]
Source: "dist\hotel_it_suite.exe"; DestDir: "{app}"; Flags: ignoreversion
Source: "app\config\settings.json"; DestDir: "{app}\config"; Flags: onlyifdoesntexist
Source: "app\config\admin_policy.json"; DestDir: "{app}\config"; Flags: onlyifdoesntexist

[Icons]
Name: "{group}\Hotel IT Suite"; Filename: "{app}\hotel_it_suite.exe"

[Run]
Filename: "{app}\hotel_it_suite.exe"; Description: "Hotel IT Suite başlat"; Flags: nowait postinstall skipifsilent
