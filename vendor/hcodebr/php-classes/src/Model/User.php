<?php
    namespace Hcode\Model;

    use \Hcode\DB\Sql;
    use \Hcode\Model;


    class User extends Model{

        const SESSION = "user";
        const SECRET1 = "HcodePhp7_Secret";
        const SECRET_IV = "WendelLuizSecret";

        public static function getFromSession(){
            $user = new User();
            if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"]>0){
               
                $user->setData($_SESSION[User::SESSION]);    
            }
            return $user;
        }

        public static function checkLogin($inadmin = true){
            if(!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] || !(int)$_SESSION[User::SESSION]["iduser"]>0){
                //Não está logado
                return false;
            }else{
                if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true ){
                    return true;
                }else if($inadmin === false){
                    return true;
                }else {
                    return false;
                }
            }
            
        }


        public static function login ($login,$password){
            $sql = new Sql();

            $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN",array(
                ":LOGIN" => $login
            ));

            if (count($results)===0){
                throw new \Exception("Usuario Inexistente ou Senha Invalida", 1);
                
            }

            $data = $results[0];

            if  (password_verify($password,$data["despassword"]) === true){
                $user = new User();
                $user -> setData($data);
                $_SESSION[User::SESSION] = $user->getValues();
                return $user;
            }else{
                throw new \Exception("Usuario Inexistente ou Senha Invalida", 1);
            }


        }




        public static function verifyLogin($inadmin = true){
            if  (User::checkLogin($inadmin)){
                header ("Location: /admin/login");
                exit;
            }


        }

        public static function logout (){
           $_SESSION[User::SESSION]=null;
        }

        public static function listAll (){
            $sql = new Sql();
            return $sql -> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) order by b.desperson");

        }


        public function save (){
            $sql = new Sql();
            $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
                ":desperson" => $this->getdesperson(),
                ":deslogin"=> $this->getdeslogin(),
                ":despassword" => $this->getdespassword(),
                ":desemail" => $this->getdesemail(),
                ":nrphone" => $this->getnrphone(),
                ":inadmin" => $this->getinadmin()
            ));

            $this->setData($results[0]);
        }

        public function get($iduser){
            $sql = new Sql();
            $results = $sql->select ("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(
                ":iduser" => $iduser
            ));

            $this->setData($results[0]);

        }

        public function update(){
            $sql = new Sql();
            $results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
                ":iduser" => $this->getiduser(),
                ":desperson" => $this->getdesperson(),
                ":deslogin"=> $this->getdeslogin(),
                ":despassword" => $this->getdespassword(),
                ":desemail" => $this->getdesemail(),
                ":nrphone" => $this->getnrphone(),
                ":inadmin" => $this->getinadmin()
            ));

            $this->setData($results[0]);

        }

        public function delete (){
            $sql = new Sql();
            $sql-> query("CALL sp_users_delete(:iduser)",array(
                ":iduser" => $this->getiduser()
            ));
        }

        public static function getForgot($email){
            $sql = new Sql();
            $resultus = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_users b USING(idperson) WHERE a.desemail =:email",array(":email"=>$email));

            if (count($results)===0){
                throw new \Exception("Não foi possível recuperar a senha");
                

            }else{
                $data = $results[0];
                $results2 = $sql->select ("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",array(
                    ":iduser" => $data["iduser"],
                    ":desip" => $_SERVER["REMOTE_ADDR"]
                ));

                if (count($results2)===0){
                    throw new \Exception("Não foi possível recuperar a senha");
                }else{
                    $dataRecovery = $results2[0];

                    $code = openssl_encrypt(
                        $dataRecovery["idrecovery"],//Quem vou criptografar
                        'AES-128-ECB',//Tipo de Criptografia
                        SECRET,//Primeira senha secreta
                        0,//se vai retornar algo alem da informação criptografada
                        SECRET_IV//segunda senha secreta IV

                        
                        // CBC
                    );
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                    
                }

            }

        }


    }




?>