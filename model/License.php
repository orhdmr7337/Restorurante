<?php

class License extends Connection
{
    private $apiUrl = "http://localhost/license-manager/api/validate.php";

    public function validateLicense($key)
    {
        // Veritabanından lisans bilgisini al
        $query = "SELECT * FROM license WHERE license_key = :key LIMIT 1";
        $stmt = $this->con->prepare($query);
        $stmt->execute([':key' => $key]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$license) {
            return ['valid' => false, 'message' => 'Lisans bulunamadı'];
        }

        // Süre kontrolü
        if ($license['expiry_date'] && strtotime($license['expiry_date']) < time()) {
            $this->updateStatus($license['id'], 'expired');
            return ['valid' => false, 'message' => 'Lisans süresi dolmuş'];
        }

        // Durum kontrolü
        if ($license['status'] != 'active') {
            return ['valid' => false, 'message' => 'Lisans aktif değil'];
        }

        // API ile doğrulama (opsiyonel)
        $apiValidation = $this->validateWithAPI($key);
        
        return [
            'valid' => true,
            'license' => $license,
            'api_check' => $apiValidation
        ];
    }

    private function validateWithAPI($key)
    {
        try {
            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['license_key' => $key]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200 && $response) {
                return json_decode($response, true);
            }
        } catch (Exception $e) {
            // API erişilemiyorsa local kontrole devam et
        }
        return ['api_available' => false];
    }

    public function activateLicense($key, $companyName)
    {
        // Önce lisansı kontrol et
        $validation = $this->validateLicense($key);
        
        if (!$validation['valid']) {
            return $validation;
        }

        // Aktivasyon tarihini güncelle
        $query = "UPDATE license SET 
                  company_name = :company_name, 
                  activation_date = CURDATE(), 
                  status = 'active' 
                  WHERE license_key = :key";
        $stmt = $this->con->prepare($query);
        $result = $stmt->execute([
            ':company_name' => $companyName,
            ':key' => $key
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Lisans başarıyla aktive edildi'];
        }
        return ['success' => false, 'message' => 'Aktivasyon başarısız'];
    }

    public function checkExpiry()
    {
        $query = "SELECT * FROM license WHERE status = 'active' LIMIT 1";
        $license = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);

        if (!$license) {
            return ['expired' => true, 'message' => 'Lisans bulunamadı'];
        }

        if ($license['expiry_date']) {
            $expiryDate = strtotime($license['expiry_date']);
            $today = time();
            $daysLeft = floor(($expiryDate - $today) / 86400);

            if ($daysLeft < 0) {
                $this->updateStatus($license['id'], 'expired');
                return ['expired' => true, 'message' => 'Lisans süresi dolmuş'];
            }

            if ($daysLeft <= 30) {
                return [
                    'expired' => false, 
                    'warning' => true, 
                    'days_left' => $daysLeft,
                    'message' => "Lisansınızın süresi {$daysLeft} gün içinde dolacak"
                ];
            }

            return ['expired' => false, 'days_left' => $daysLeft];
        }

        // Ömür boyu lisans
        return ['expired' => false, 'lifetime' => true];
    }

    public function getFeatures()
    {
        $query = "SELECT features FROM license WHERE status = 'active' LIMIT 1";
        $result = $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['features']) {
            return json_decode($result['features'], true);
        }
        return [];
    }

    public function hasFeature($featureName)
    {
        $features = $this->getFeatures();
        return isset($features[$featureName]) && $features[$featureName] === true;
    }

    private function updateStatus($id, $status)
    {
        $query = "UPDATE license SET status = :status WHERE id = :id";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getCurrentLicense()
    {
        $query = "SELECT * FROM license WHERE status = 'active' LIMIT 1";
        return $this->con->query($query)->fetch(PDO::FETCH_ASSOC);
    }

    public function saveLicense($key)
    {
        // Mevcut lisansları pasif yap
        $this->con->exec("UPDATE license SET status = 'suspended'");

        // Yeni lisans ekle
        $query = "INSERT INTO license (license_key, status) VALUES (:key, 'active')";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([':key' => $key]);
    }
}
