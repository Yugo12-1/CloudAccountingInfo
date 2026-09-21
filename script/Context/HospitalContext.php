<?php

class HospitalContext {

    private string $pHospitalKey;
    private string $pHospitalID;
    private string $pDisplayName;
    private string $pGroupID;

    private string $pDbHostName;
    private string $pDbName;
    private string $pDbUserName;
    private string $pDbPassword;
    private string $pDbCharset;


    public function __construct(
        string $hospitalKey,
        string $hospitalID,
        string $displayName,
        string $groupID,
        string $dbHostName,
        string $dbName,
        string $dbUserName,
        string $dbPassword,
        string $dbCharset
    ) {
        $this->pHospitalKey = $hospitalKey;
        $this->pHospitalID = $hospitalID;
        $this->pDisplayName = $displayName;
        $this->pGroupID = $groupID;

        $this->pDbHostName = $dbHostName;
        $this->pDbName = $dbName;
        $this->pDbUserName = $dbUserName;
        $this->pDbPassword = $dbPassword;
        $this->pDbCharset = $dbCharset;
    }


    public function gfGetHospitalKey() : string {
        return $this->pHospitalKey;
    }

    public function gfGetHospitalID() : string {
        return $this->pHospitalID;
    }

    public function gfGetDisplayName() : string {
        return $this->pDisplayName;
    }

    public function gfGetGourpID() : string {
        return $this->pGroupID;
    }

    public function gfGetDbHostName() : string {
        return $this->pDbHostName;
    }

    public function gfGetDbName() : string {
        return $this->pDbName;
    }

    public function gfGetDbUserName() : string {
        return $this->pDbUserName;
    }

    public function gfGetDbPassword() : string {
        return $this->pDbPassword;
    }

    public function gfGetDbCharset() : string {
        return $this->pDbCharset;
    }
}