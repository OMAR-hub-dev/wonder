<?php
namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    public function __construct(private Filesystem $fs, private $profileFolder, private $profileFolderPublic)
    {
    }

    public function uploadProfileImage(UploadedFile $picture, string $oldPicturePath = null): string
    {
        //  nous récupérons le paramètre de service que nous venons de définir dans la configuration. 
        $folder = $this->profileFolder;
        $ext = $picture->guessExtension() ?? 'bin';
        // nous créons un nom de fichier aléatoire qui fait 10 octets que nous convertissons en hexadécimal. En hexadécimal (Base16), chaque caractère correspond à 4 bits donc 2 caractères correspondent à 1 octet. Le nom des fichiers sera donc de 20 caractères puis un point puis l'extension. 
        $filename = bin2hex(random_bytes(10)) . '.' . $ext;
        //  permet de déplacer l'image obtenue avec le formulaire depuis l'espace temporaire vers le dossier que nous avons défini et avec notre nom aléatoire. 
        $picture->move($folder, $filename);
        // permet de supprimer la précédente photo de profile 
        if ($oldPicturePath) {
            $this->fs->remove($folder . '/' . pathinfo($oldPicturePath, PATHINFO_BASENAME));
        }
    
        return $this->profileFolderPublic . '/' . $filename;
    }
}