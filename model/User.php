<?php
class User extends Connection
{
    public function getAllUsers(){
        $users = $this->con->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getOneUser($userId){
        $user = $this->con->query("SELECT * FROM users WHERE id = ". $userId)->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function getAllUserCount(){
        $getAllUsersCount = $this->con->query('SELECT COUNT(id) FROM users')->fetch(PDO::FETCH_ASSOC);
        return $getAllUsersCount;
   }

    public function registerUser($username,$password,$email,$fullname,$userPosition, $roleId = null){
        //Veritabanında aynı username veya email varsa false döndürecek
        $isThereUser = $this->con->prepare("SELECT * FROM users WHERE username=:username OR email=:email LIMIT 1");
        $isThereUser->execute(array(':username'=>$username, ':email'=>$email));
        if($isThereUser->rowCount() > 0) {
           return false;
        }else {
        // Şifreyi hash'le (güvenlik)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $UserRegister = $this->con->prepare("INSERT INTO users(username,password,email,fullname,user_position,role_id) VALUES(:username, :password, :email, :fullname, :userPosition, :roleId)");
        $UserRegister->bindParam(":username", $username);
        $UserRegister->bindParam(":password", $hashedPassword);
        $UserRegister->bindParam(":email", $email);
        $UserRegister->bindParam(":fullname", $fullname);
        $UserRegister->bindParam(":userPosition", $userPosition);
        $UserRegister->bindParam(":roleId", $roleId);
        $UserRegister->execute();
        }

        return $UserRegister;

    }

    public function userUpdate($userId,$username,$email,$fullname,$userPosition){
        $edit = $this->con->prepare("UPDATE users SET username=?, email=?, fullname=?, user_position=? WHERE id=$userId");
        $cntrl = $edit->execute(array($username, $email, $fullname,$userPosition));

        if($cntrl)
            return true;
        return false;

    }

    public function userDelete($userId)
    {
        $del = $this->con->exec("DELETE FROM users WHERE id=" . $userId);

        if ($del)
            return true;
        return false;
    }

   // public function login($username,$password){ // Sadece kullanıcı adı ve şifre kontrol etmek istenirse
    public function login($username,$password,$email){
       $isThereUser = $this->con->prepare("SELECT * FROM users WHERE username=:username OR email=:email LIMIT 1");
        $isThereUser->execute(array(':username'=>$username, ':email'=>$email));

        $userRow=$isThereUser->fetch(PDO::FETCH_ASSOC);
        if($isThereUser->rowCount() > 0)
        {
            // Önce bcrypt hash kontrolü yap
            if(password_verify($password, $userRow['password'])) {
                $_SESSION['user_session'] = $userRow['id'];
                $_SESSION['user_role'] = $userRow['role_id'];
                $_SESSION['user_position'] = $userRow['user_position'];
                return true;
            }
            // Eski MD5 şifreler için geriye dönük uyumluluk
            elseif($password == $userRow['password'] || md5($password) == $userRow['password'])
            {
                $_SESSION['user_session'] = $userRow['id'];
                $_SESSION['user_role'] = $userRow['role_id'];
                $_SESSION['user_position'] = $userRow['user_position'];

                // Şifreyi yeni hash ile güncelle
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $this->con->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updateStmt->execute([':password' => $newHash, ':id' => $userRow['id']]);

                return true;
            }
            else
            {
                return false;
            }
        }
        return false;
    }

    public function isLoggedIn()
    {
        if(isset($_SESSION['user_session']))
        {
            return true;
        }
    }

    public function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    public function logOut()
    {
        session_destroy();
        unset($_SESSION['user_session']);
        return true;
    }

    public function updatePassword($userId, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->con->prepare("UPDATE users SET password = :password WHERE id = :id");
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteUser($userId)
    {
        try {
            // Check if user exists and is not the last admin
            $userCheck = $this->con->prepare("SELECT user_position FROM users WHERE id = :id");
            $userCheck->execute([':id' => $userId]);
            $user = $userCheck->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            // If this is an admin, check if there are other admins
            if ($user['user_position'] == 1) {
                $adminCount = $this->con->prepare("SELECT COUNT(*) as count FROM users WHERE user_position = 1 AND id != :id");
                $adminCount->execute([':id' => $userId]);
                $count = $adminCount->fetch(PDO::FETCH_ASSOC);

                if ($count['count'] == 0) {
                    // Cannot delete the last admin
                    return false;
                }
            }

            $stmt = $this->con->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute([':id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserByUsername($username)
    {
        try {
            $stmt = $this->con->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserByEmail($email)
    {
        try {
            $stmt = $this->con->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateLastActivity($userId)
    {
        try {
            $stmt = $this->con->prepare("UPDATE users SET last_activity = NOW() WHERE id = :id");
            return $stmt->execute([':id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUsersByRole($role)
    {
        try {
            $stmt = $this->con->prepare("SELECT * FROM users WHERE user_position = :role ORDER BY fullname ASC");
            $stmt->execute([':role' => $role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function searchUsers($searchTerm, $limit = 20, $offset = 0)
    {
        try {
            $searchTerm = '%' . $searchTerm . '%';
            $stmt = $this->con->prepare("
                SELECT * FROM users
                WHERE username LIKE :search
                   OR email LIKE :search
                   OR fullname LIKE :search
                ORDER BY fullname ASC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':search', $searchTerm);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getUsersCount()
    {
        try {
            $stmt = $this->con->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function toggleUserStatus($userId, $status)
    {
        try {
            $stmt = $this->con->prepare("UPDATE users SET status = :status WHERE id = :id");
            return $stmt->execute([
                ':status' => $status ? 1 : 0,
                ':id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

}
