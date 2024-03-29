<?php

namespace Labstag\Entity\Traits\Paragraph;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\History;
use Labstag\Entity\Paragraph\History as ParagraphHistory;
use Labstag\Entity\Paragraph\History\Chapter as HistoryChapter;
use Labstag\Entity\Paragraph\History\Liste as HistoryList;
use Labstag\Entity\Paragraph\History\Show as HistoryShow;
use Labstag\Entity\Paragraph\History\User as HistoryUser;

trait HistoryEntity
{

    #[ORM\OneToMany(
        targetEntity: ParagraphHistory::class,
        mappedBy: 'paragraph',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $histories;

    #[ORM\ManyToOne(
        targetEntity: History::class,
        inversedBy: 'paragraphs',
        cascade: ['persist']
    )
    ]
    private ?History $history = null;

    #[ORM\OneToMany(
        targetEntity: HistoryChapter::class,
        mappedBy: 'paragraph',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $historyChapters;

    #[ORM\OneToMany(
        targetEntity: HistoryList::class,
        mappedBy: 'paragraph',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $historyLists;

    #[ORM\OneToMany(
        targetEntity: HistoryShow::class,
        mappedBy: 'paragraph',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $historyShows;

    #[ORM\OneToMany(
        targetEntity: HistoryUser::class,
        mappedBy: 'paragraph',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $historyUsers;

    public function addHistory(ParagraphHistory $paragraphHistory): self
    {
        if (!$this->histories->contains($paragraphHistory)) {
            $this->histories[] = $paragraphHistory;
            $paragraphHistory->setParagraph($this);
        }

        return $this;
    }

    public function addHistoryChapter(HistoryChapter $historyChapter): self
    {
        if (!$this->historyChapters->contains($historyChapter)) {
            $this->historyChapters[] = $historyChapter;
            $historyChapter->setParagraph($this);
        }

        return $this;
    }

    public function addHistoryList(HistoryList $historyList): self
    {
        if (!$this->historyLists->contains($historyList)) {
            $this->historyLists[] = $historyList;
            $historyList->setParagraph($this);
        }

        return $this;
    }

    public function addHistoryShow(HistoryShow $historyShow): self
    {
        if (!$this->historyShows->contains($historyShow)) {
            $this->historyShows[] = $historyShow;
            $historyShow->setParagraph($this);
        }

        return $this;
    }

    public function addHistoryUser(HistoryUser $historyUser): self
    {
        if (!$this->historyUsers->contains($historyUser)) {
            $this->historyUsers[] = $historyUser;
            $historyUser->setParagraph($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, History>
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    /**
     * @return Collection<int, HistoryChapter>
     */
    public function getHistoryChapters(): Collection
    {
        return $this->historyChapters;
    }

    /**
     * @return Collection<int, HistoryList>
     */
    public function getHistoryLists(): Collection
    {
        return $this->historyLists;
    }

    /**
     * @return Collection<int, HistoryShow>
     */
    public function getHistoryShows(): Collection
    {
        return $this->historyShows;
    }

    /**
     * @return Collection<int, HistoryUser>
     */
    public function getHistoryUsers(): Collection
    {
        return $this->historyUsers;
    }

    public function removeHistory(ParagraphHistory $paragraphHistory): self
    {
        $this->removeElementHistory(
            element: $this->histories,
            paragraphHistory: $paragraphHistory
        );

        return $this;
    }

    public function removeHistoryChapter(HistoryChapter $historyChapter): self
    {
        $this->removeElementHistory(
            element: $this->historyChapters,
            historyChapter: $historyChapter
        );

        return $this;
    }

    public function removeHistoryList(HistoryList $historyList): self
    {
        $this->removeElementHistory(
            element: $this->historyLists,
            historyList: $historyList
        );

        return $this;
    }

    public function removeHistoryShow(HistoryShow $historyShow): self
    {
        $this->removeElementHistory(
            element: $this->historyShows,
            historyShow: $historyShow
        );

        return $this;
    }

    public function removeHistoryUser(HistoryUser $historyUser): self
    {
        $this->removeElementHistory(
            element: $this->historyUsers,
            historyUser: $historyUser
        );

        return $this;
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history;

        return $this;
    }

    private function removeElementHistory(
        Collection $element,
        ?ParagraphHistory $paragraphHistory = null,
        ?HistoryChapter $historyChapter = null,
        ?HistoryList $historyList = null,
        ?HistoryShow $historyShow = null,
        ?HistoryUser $historyUser = null
    ): void
    {
        $variable = is_null($paragraphHistory) ? null : $paragraphHistory;
        $variable = is_null($historyChapter) ? $variable : $historyChapter;
        $variable = is_null($historyList) ? $variable : $historyList;
        $variable = is_null($historyShow) ? $variable : $historyShow;
        $variable = is_null($historyUser) ? $variable : $historyUser;
        if (!is_null($variable) && $element->removeElement($variable) && $variable->getParagraph() === $this) {
            $variable->setParagraph(null);
        }
    }
}
