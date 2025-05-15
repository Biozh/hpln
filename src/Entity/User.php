<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['video:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email(message: "L'adresse email est invalide.")]
    #[Assert\NotBlank(message: "L'adresse email est obligatoire.")]
    #[Groups(['video:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[\p{L} '-]+$/u",
        message: "Le prénom contient des caractères non autorisés."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le prénom ne doit pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['video:read'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[\p{L} '-]+$/u",
        message: "Le nom contient des caractères non autorisés."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['video:read'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'adresse ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La ville est obligatoire.")]
    #[Assert\NotNull(message: "La ville ne peut pas être nulle.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "La ville ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $city = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: "Le code postal est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 5,
        maxMessage: "Le code postal doit faire {{limit}} caractères."
    )]
    private ?string $zip = null;

    #[Ignore]
    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'pictureName', originalName: 'pictureOriginalName')]
    #[Assert\File(
        maxSize: '1M',
        maxSizeMessage: 'La taille du fichier ne doit pas dépasser 1 Mo.',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Veuillez sélectionner une image valide (jpeg, png, webp).'
    )]
    private ?File $picture = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['video:read'])]
    private ?string $pictureName = null;

    #[ORM\Column(nullable: true)]
    private ?string $pictureOriginalName = null;


    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = ["ROLE_USER"];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sidebar = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): static
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles = ['ROLE_USER']): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->picture = null;
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSidebar(): ?string
    {
        return $this->sidebar;
    }

    public function setSidebar(?string $sidebar): static
    {
        $this->sidebar = $sidebar;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $picture
     */
    public function setPicture(?File $picture = null): void
    {
        $this->picture = $picture;
        if (null !== $picture) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPicture(): ?File
    {
        return $this->picture;
    }

    public function setPictureName(?string $pictureName): void
    {
        $this->pictureName = $pictureName;
    }

    public function getPictureName(): ?string
    {
        return $this->pictureName;
    }

    public function setPictureOriginalName(?string $pictureOriginalName): void
    {
        $this->pictureOriginalName = $pictureOriginalName;
    }

    public function getPictureOriginalName(): ?string
    {
        return $this->pictureOriginalName;
    }
}
