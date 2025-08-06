<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# HIDRAMANI STICKER PRINTER - BACKEND

This is the **Laravel-based backend** for the **Hidramani Sticker Printer** system, developed specifically for the **Swatch Handover Sticker Printing Process** at **Hidramani (EGDC)**. It facilitates seamless data handling, label generation, and printing through a web-based portal, while also providing API support for a companion Flutter mobile app. The system ensures proper handover tracking, eliminates manual sticker management, and enables role-based access for streamlined operations.

## 🔧 Features

- 📁 **Excel Upload**  
  Users can upload swatch handover data in bulk via Excel sheets, simplifying mass entry and reducing human error.

- 🖨️ **Label Print Status**  
  The system tracks which entries have already been printed and which are pending, enabling better process control.

- 🔁 **Reprint Support**  
  Authorized roles can easily reprint any previously printed stickers when required.

- 🔍 **Search and Filter**  
  Quickly locate entries using filters like status, uploaded date, and keywords.

- 👥 **Role-Based Access Control**  
  System access is defined by roles (Admin, Operation, and User), ensuring data integrity and operational discipline.

- 🗑️ **Admin Data Management**  
  Admins can manage users, delete records, and maintain overall control over the system.

- 📊 **Dashboard Overview**  
  A web dashboard shows a summary of uploaded, printed, and pending items for better visibility and tracking.


  ## 🧑‍💼 User Roles

| Role        | Permissions                                     |
|-------------|--------------------------------------------------|
| **Admin**   | Full access: manage users, data, settings, and reprints |
| **Operation** | Can view data and use the reprint function       |
| **User**    | Can only upload Excel files and view their data   |


## 🛠️ Tech Stack

- **Backend Framework**: Laravel 11+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Frontend**: Connected via [Flutter Mobile App (Separate Repository)](https://github.com/your_flutter_repo_link)

## Screenshots

<p align="center">
  <img src="https://github.com/user-attachments/assets/eb6438f1-2695-4166-b99b-f03bc6541cc9" width="100%" alt="Screenshot 1"/>
</p>

<br/>

<p align="center">
  <img src="https://github.com/user-attachments/assets/556d2487-cd3e-4cba-91b7-746ac2f40161" width="100%" alt="Screenshot 2"/>
</p>

<br/>

<p align="center">
  <img src="https://github.com/user-attachments/assets/7710254b-cb24-4a00-aea5-0039d82bdac7" width="100%" alt="Screenshot 3"/>
</p>

<br/>

<p align="center">
  <img src="https://github.com/user-attachments/assets/efc938a1-cb2d-4496-b956-2b191b038be7" width="100%" alt="Screenshot 4"/>
</p>




