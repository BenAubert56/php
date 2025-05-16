<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Response\Comment\CommentResponse;

class CommentService
{
    public function __construct(private CommentRepository $commentRepository) {}

    public function create(string $content, Tweet $tweet, User $author): CommentResponse
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setTweet($tweet);
        $comment->setAuthor($author);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->commentRepository->save($comment);

        return new CommentResponse($comment);
    }

    public function delete(Comment $comment): void
    {
        $this->commentRepository->remove($comment);
    }

    public function getByTweet(Tweet $tweet): array
    {
        return array_map(
            fn(Comment $comment) => new CommentResponse($comment),
            $this->commentRepository->findByTweet($tweet)
        );
    }
}
