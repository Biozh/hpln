<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[Vich\Uploadable]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['video:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['video:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['video:read'])]
    private ?string $description = null;
    
    #[Ignore]
    #[Vich\UploadableField(mapping: 'videos', fileNameProperty: 'pictureName', originalName: 'pictureOriginalName')]
    private ?File $picture = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['video:read'])]
    private ?string $pictureName = null;

    #[ORM\Column(nullable: true)]
    private ?string $pictureOriginalName = null;


    #[ORM\Column(nullable: false)]
    #[Groups(['video:read'])]
    private ?string $url = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'videos')]
    #[Groups(['video:read'])]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['video:read'])]
    private ?VideoCategory $category = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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


    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
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

    public function getCategory(): ?VideoCategory
    {
        return $this->category;
    }

    public function setCategory(?VideoCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

}
