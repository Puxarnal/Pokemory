<?php

namespace Pokemory\Model;

/**
 * Représente un Pokémon
 */
class Pokemon
{
    /**
     * @property int $id Identifiant du Pokémon en base de données
     */
    protected $id;

    /**
     * @property string $name Nom international du Pokémon
     */
    protected $name;

    /**
     * @property string $fr Nom français du Pokémon, formaté pour l'affichage
     */
    protected $fr;

    /**
     * @property string $img URL de l'image du Pokémon
     */
    protected $img;

    /**
     * Retourne l'id du Pokémon s'il est enregistré en base de données
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom international du Pokémon (non formaté)
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Renseigne le nom international du Pokémon (non formaté)
     * @param string $name Le nom du Pokémon
     * @return static L'objet Pokemon pour chaîner les appels
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Retourne le nom français du Pokémon pour l'affichage
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->fr;
    }

    /**
     * Renseigne le nom français du Pokémon destiné à l'affichage
     * @param string $display_name Le nom du Pokémon, en français, pour l'affichage
     * @return static L'objet Pokemon pour chaîner les appels
     */
    public function setDisplayName(string $display_name): self
    {
        $this->fr = $display_name;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->img;
    }

    /**
     * Renseigne l'URL de l'image du Pokémon
     * @param string $img_url L'URL de l'image
     * @return static L'objet Pokemon pour chaîner les appels
     */
    public function setImageUrl(string $img_url): self
    {
        $this->img = $img_url;

        return $this;
    }
}