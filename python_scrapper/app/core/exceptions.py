class RetryableError(Exception):
    pass

class PermanentError(Exception):
    pass

class TokenExpiredError(Exception):
    pass