<?php
session_start();
$conn = mysqli_connect("localhost","root","","stockbaranghambers");

//add barang
if(isset($_POST['addnewbarang'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];

    //Image input
    $allowed_extension = array('png','jpg','jpeg');
    $namagambar = $_FILES['file']['name'];
    $dot = explode('.',$namagambar);
    $ekstensi = strtolower(end($dot));
    $ukuran = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];//lokasi file

    //penamaan file -> enkripsi
    $image = md5(uniqid($namagambar,true) . time()).'.'.$ekstensi;//menggabungkan nama file dgn eksrtensi


    $cek = mysqli_query($conn,"select * from stockbarang where namabarang='$namabarang'");
    $hitung = mysqli_num_rows($cek);

    if($hitung < 1){
        if(in_array($ekstensi, $allowed_extension)===true){
            if($ukuran < 15000000){
                move_uploaded_file($file_tmp, 'images/'. $image);

                $addtotable = mysqli_query($conn,"insert into stockbarang (namabarang, deskripsi, stock, image) values('$namabarang','$deskripsi','$stock','$image')");
                if($addtotable){
                    header('location:index.php');
                }else{
                    echo 'Gagal';
                    header('location:index.php');
                };
            }else{
                //kalau filenya melebihi
                echo '
                <script>
                    alert("Ukuran Terlalu kecil");
                    window.location="index.php"
                </script>
                ';
            }     
        }else{
            //kalau gambar tidak png/jpg
            echo '
            <script>
                alert("Ukuran Terlalu Besar");
                window.location="index.php"
            </script>
            ';
        }
    
}else{
    echo '
    <script>
        alert("Ukuran Terlalu Ruwet");
        window.location="index.php"
        </script>
    ';
}
};

//Input barang
if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn,"select * from stockbarang where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $tambahkanstocksekarangdenganquantity = $stocksekarang + $qty;

    $addtomasuk = mysqli_query($conn,"insert into barangmasuk (idbarang, keterangan, qty) values('$barangnya','$penerima','$qty')");
    $updatestockmasuk = mysqli_query($conn,"update stockbarang set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
    if($addtomasuk&&$updatestockmasuk){
        header('location:masuk.php');
    }else{
        echo 'Gagal';
        header('location:masuk.php');
    }
}

//Input barang keluar
if(isset($_POST['barangkeluar'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn,"select * from stockbarang where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];

    if($stocksekarang >= $qty){
        $tambahkanstocksekarangdenganquantity = $stocksekarang - $qty;

        $addtokeluar = mysqli_query($conn,"insert into barangkeluar (idbarang, penerima, qty) values('$barangnya','$penerima','$qty')");
        $updatestockmasuk = mysqli_query($conn,"update stockbarang set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
        if($addtokeluar&&$updatestockmasuk){
            header('location:keluar.php');
        }else{
            echo 'Gagal';
            header('location:keluar.php');
        }
    }else{
        echo'
        <script>
            alert("Current stock are not enough");
            window.location.href="keluar.php";
        </script>
        ';
    }
}
 
//Update info barang
if(isset($_POST['updatebarang'])){
    $idb = $_POST['idbarang'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];

    $allowed_extension = array('png','jpg','jpeg');
    $namagambar = $_FILES['file']['name'];
    $dot = explode('.',$namagambar);
    $ekstensi = strtolower(end($dot));
    $ukuran = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];//lokasi file

    //penamaan file -> enkripsi
    $image = md5(uniqid($namagambar,true) . time()).'.'.$ekstensi;

    if($ukuran==0){
        //jika tidak ingin upload
        $update = mysqli_query($conn,"update stockbarang set namabarang='$namabarang', deskripsi='$deskripsi' where idbarang='$idb'");
        if($update){
            header('location:index.php');
        }else{
            echo 'Gagal';
            header('location:index.php');
        }
    }else{
        //jika ingin uplodad
        move_uploaded_file($file_tmp, 'images/'. $image);

        $update = mysqli_query($conn,"update stockbarang set namabarang='$namabarang', deskripsi='$deskripsi', image='$image' where idbarang='$idb'");
        if($update){
            header('location:index.php');
        }else{
            echo 'Gagal';
            header('location:index.php');
        }
    }
}

//Delete item stock
if(isset($_POST['hapusbarang'])){
    $idb = $_POST['idbarang'];

    $gambar = mysqli_query($conn,"select * from stockbarang where idbarang='$idb'");
    $get = mysqli_fetch_array($gambar);
    $img = 'images/'.$get['image'];
    unlink($img);

    $hapus = mysqli_query($conn, "delete from stockbarang where idbarang = '$idb'");
    if($hapus){
        header('location:index.php');
    }else{
        echo 'Gagal';
        header('location:index.php');
    }
};

//mengubah data barang masuk
if(isset($_POST['updatebarangmasuk'])){
    $idb = $_POST['idbarang'];
    $idm = $_POST['idmasuk'];
    $deskripsi = $_POST['keterangan'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn,"select * from stockbarang where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stocknya['stock'];

    $qtyskrg = mysqli_query($conn,"select * from barangmasuk where idmasuk='$idm'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty - $qtyskrg;
        $kurangin = $stockskrg + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stockbarang set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update barangmasuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:masuk.php');
                }else{
                    echo 'Gagal';
                    header('location:masuk.php');
                }
    }else{
        $selisih = $qtyskrg - $qty;
        $kurangin = $stockskrg - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stockbarang set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update barangmasuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:masuk.php');
                }else{
                    echo 'Gagal';
                    header('location:masuk.php');
                }
    }
};

//menghapus barang masuk
if(isset($_POST['hapusbarangmasuk'])){
    $idb = $_POST['idbarang'];
    $qty = $_POST['kty'];
    $idm = $_POST['idmasuk'];

    $getdatastock = mysqli_query($conn,"select * from stockbarang where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stok = $data['stock'];

    $selisih = $stok - $qty;

    $update = mysqli_query($conn,"update stockbarang set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn,"delete from barangmasuk where idmasuk='$idm'");

    if($update&&$hapusdata){
        header('location:masuk.php');
    }else{

    }
}

//mengubah data barang keluar
if(isset($_POST['updatebarangkeluar'])){
    $idb = $_POST['idbarang'];
    $idk = $_POST['idkeluar'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn,"select * from stockbarang where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stocknya['stock'];

    $qtyskrg = mysqli_query($conn,"select * from barangkeluar where idkeluar='$idk'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty - $qtyskrg;
        $kurangin = $stockskrg - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stockbarang set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update barangkeluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya&&$updatenya){
                header('location:keluar.php');
                }else{
                    echo 'Gagal';
                    header('location:keluar.php');
                }
    }else{
        $selisih = $qtyskrg - $qty;
        $kurangin = $stockskrg + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stockbarang set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update barangkeluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya&&$updatenya){
                header('location:keluar.php');
                }else{
                    echo 'Gagal';
                    header('location:keluar.php');
                }
    }
};

//menghapus barang keluar
if(isset($_POST['hapusbarangkeluar'])){
    $idb = $_POST['idbarang'];
    $qty = $_POST['kty'];
    $idk = $_POST['idkeluar'];

    $getdatastock = mysqli_query($conn,"select * from stockbarang where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stok = $data['stock'];

    $selisih = $stok + $qty;

    $update = mysqli_query($conn,"update stockbarang set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn,"delete from barangkeluar where idkeluar='$idk'");

    if($update&&$hapusdata){
        header('location:keluar.php');
    }else{
        header('location:keluar.php');
    }
}

//Menambah admin baru
if(isset($_POST['addadmin'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $queryinsert = mysqli_query($conn,"insert into login (Username, Password) values ('$username','$password')");

    if($queryinsert){
        header('location:admin.php');
    }else{
        header('location:admin.php');
    }
}

//edit data admin
if(isset($_POST['updateadmin'])){
    $usernamebaru = $_POST['usernamebaru'];
    $passbaru = $_POST['passbaru'];
    $iduser = $_POST['iduser'];

    $queryupdate = mysqli_query($conn,"update login set Username='$usernamebaru', Password='$passbaru' where iduser='$iduser'");

    if($queryupdate){
        header('location:admin.php');
    }else{
        header('location:admin.php');
    }

}

//hapus data admin
if(isset($_POST['hapusadmin'])){
    $iduser = $_POST['iduser'];

    $querydelete = mysqli_query($conn,"delete from login where iduser='$iduser'");

    if($querydelete){
        header('location:admin.php');
    }else{
        header('location:admin.php');
    }
}

//meminjam barang
if(isset($_POST['pinjam'])){
    $idbarang = $_POST['barangnya'];
    $qty = $_POST['qty'];
    $penerima = $_POST['penerima'];

    $stockuntukpinjam = mysqli_query($conn,"select * from stockbarang where idbarang='$idbarang'");
    $stoknya = mysqli_fetch_array($stockuntukpinjam);
    $stokpinjam = $stoknya['stock'];

    $newstockpinjam = $stokpinjam - $qty;

    $insertpinjam = mysqli_query($conn,"INSERT INTO peminjaman (idbarang, qty, peminjam) values('$idbarang','$qty','$penerima')");

    $kurangistock = mysqli_query($conn, "update stockbarang set stock='$newstockpinjam' where idbarang='$idbarang'");
    
    if($insertpinjam && $kurangistock){
        echo'
        <script>
            alert("Berhasil");
            window.location.href="peminjaman.php";
        </script>
        ';
    }else{
        echo'
        <script>
            alert("Gagal");
            window.location.href="peminjaman.php";
        </script>
        ';
    }
}

//menyelesaikan pinjaman  
if(isset($_POST['barangkembali'])){
    $idpinjam = $_POST['idpinjam'];
    $idbarang = $_POST['idbarang'];

    $updatestatus = mysqli_query($conn,"update peminjaman set status='kembali' where idpeminjaman='$idpinjam'");

    $stockuntukpinjam = mysqli_query($conn,"select * from stockbarang where idbarang='$idbarang'");
    $stoknya = mysqli_fetch_array($stockuntukpinjam);
    $stokpinjam = $stoknya['stock'];

    $stockpinjam = mysqli_query($conn,"select * from peminjaman where idpeminjaman='$idpinjam'");
    $stokqtypinjam = mysqli_fetch_array($stockpinjam);
    $stokpinjamkembali = $stokqtypinjam['qty'];

    $newstockpinjam = $stokpinjamkembali + $stokpinjam;

    $kembalikanstockpinjam = mysqli_query($conn,"update stockbarang set stock='$newstockpinjam' where idbarang='$idbarang'");

    if($updatestatus && $kembalikanstockpinjam){
        echo'
        <script>
            alert("Berhasil");
            window.location.href="peminjaman.php";
        </script>
        ';
    }else{
        echo'
        <script>
            alert("Gagal");
            window.location.href="peminjaman.php";
        </script>
        ';
    }
}

?>