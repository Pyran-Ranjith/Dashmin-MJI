
## Remote Connections
### User Access Demo
Database: if0_39184750_spms_demo
http://spms-demo.kesug.com/NSPMS/Nmc/

## Infinite Free
### Login
https://dash.infinityfree.com/login
- Username: pyranranjith@gmail.com
- Password: Aa$499404

### WinSCP
#### Nmc
- Transfer type: Ftp
- Hostname: ftpupload.net
- Port: 21
- Username: if0_39184750 
- Password: AaS499404
- Local Folder: C:\xampp\htdocs\JYI_DEV_6_DashMin\Demo

__________________________________________________________________________
### User Access Nmc Namarathne
Database: 
http://namarathne.lovestoblog.com/NSPMS/Nmc/

## Infinite Free
### Login
https://dash.infinityfree.com/login
- Username: ranjithimas@gmail.com
- Password: Aa$499404

### WinSCP
#### Nmc
- Transfer type: Ftp
- Hostname: ftpupload.net
- Port: 21
- Username: if0_37657216
- Password: AaS499404
- Local Folder: C:\xampp\htdocs\JYI_DEV_6_DashMin\Nmc

#### How to transfer
- Go to remote folder
    - Delete folder htdocs\NSPMS\Nmc
- Go to local folder C:\xampp\htdocs\JYI_DEV_6_DashMin
    - delete Folder Nmc
- Sigin in with master
    - Execute Batch/batch_copy_files_src_to_destination_folder_prod
        - this will create Nmc folder and its contents
    - Go to local folder C:\xampp\htdocs\JYI_DEV_6_DashMin again
        - now drag and drop local Nmc flder to Rrmote htdocs\NSPMS
- Sign in to Infinite Free as above ## Infinite Free
    - Select Tab MySQL Databases
    - delete and recreate database if0_37657216_2
    - go to phpMyadmin
    - go to Sql tab
    - copy and paste from exported local database.sql script as follows
    ``` sql 
        SET FOREIGN_KEY_CHECKS = 0;
            exported local database.sql scrip
        SET FOREIGN_KEY_CHECKS = 1;
    ```
    - run the script will create the database
__________________________________________________________________________
